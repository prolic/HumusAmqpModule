<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace HumusAmqpModule;

use AMQPEnvelope;
use AMQPQueue;
use ArrayIterator;
use InfiniteIterator;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

/**
 * The consumer attaches to a single queue
 *
 * The used block size is the configured prefetch size of the queue's channel
 */
class Consumer implements ConsumerInterface, EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    /**
     * @var InfiniteIterator
     */
    protected $queues;

    /**
     * Number of consumed messages
     *
     * @var int
     */
    protected $countMessagesConsumed = 0;

    /**
     * Number of unacked messaged
     *
     * @var int
     */
    protected $countMessagesUnacked = 0;

    /**
     * Last delivery tag seen
     *
     * @var string
     */
    protected $lastDeliveryTag;

    /**
     * @var
     */
    protected $keepAlive = true;

    /**
     * Idle timeout in seconds
     *
     * @var float
     */
    protected $idleTimeout;

    /**
     * Wait timeout in microseconds
     *
     * @var int
     */
    protected $waitTimeout;

    /**
     * The blocksize (see prefetch_count)
     *
     * @var int
     */
    protected $blockSize;

    /**
     * @var float
     */
    protected $timestampLastAck;

    /**
     * @var float
     */
    protected $timestampLastMessage;

    /**
     * Whether or not a redelivery has been detected. If so, the block will be rejected and not requeued
     * When a dead lettering exchange is defined, the message will be moved there.
     *
     * @var bool
     */
    protected $redeliverySeen = false;

    /**
     * How many messages we want to consume
     *
     * @var int
     */
    protected $target;

    /**
     * Constructor
     *
     * @param array|\Traversable $queues
     * @param float $idleTimeout in seconds
     * @param int $waitTimeout in microseconds
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($queues, $idleTimeout, $waitTimeout)
    {
        if (!is_array($queues) && !$queues instanceof \Traversable) {
            throw new Exception\InvalidArgumentException(
                'Expected an array or Traversable of queues'
            );
        }

        if (empty($queues)) {
            throw new Exception\InvalidArgumentException(
                'No queues given'
            );
        }

        $q = array();
        foreach ($queues as $queue) {
            if (!$queue instanceof AMQPQueue) {
                throw new Exception\InvalidArgumentException(
                    'Queue must be an instance of AMQPQueue, '
                    . is_object($queue) ? get_class($queue) : gettype($queue) . ' given'
                );
            }
            if (null === $this->blockSize) {
                $this->blockSize = $queue->getChannel()->getPrefetchCount();
            }
            $q[] = $queue;
        }
        $this->idleTimeout = (float) $idleTimeout;
        $this->waitTimeout = (int) $waitTimeout;
        $this->queues = new InfiniteIterator(new ArrayIterator($q));

    }

    /**
     * Get the current queue
     *
     * @return AMQPQueue
     */
    public function getQueue()
    {
        return $this->queues->current();
    }

    /**
     * Get all queues
     *
     * @return AMQPQueue[]
     */
    public function getQueues()
    {
        return iterator_to_array($this->queues->getInnerIterator());
    }

    /**
     * Start consumer
     *
     * @param int $msgAmount
     */
    public function consume($msgAmount = 0)
    {
        $this->target = $msgAmount;

        do {
            if (!$this->timestampLastAck) {
                $this->timestampLastAck = microtime(1);
            }

            $queue = $this->fetchNextQueue();
            $message = $queue->get();

            if ($message instanceof AMQPEnvelope) {
                try {
                    $processFlag = $this->handleDelivery($message, $queue);
                } catch (\Exception $e) {
                    $this->handleDeliveryException($e);
                    $processFlag = false;
                }
                $this->handleProcessFlag($message, $processFlag);
            } else {
                usleep($this->waitTimeout);
            }

            $now = microtime(1);

            if ($this->countMessagesUnacked > 0
                && ($this->countMessagesUnacked == $this->blockSize
                || ($now - $this->timestampLastAck) > $this->idleTimeout
            )) {
                $this->ackOrNackBlock();
            }

        } while ($this->keepAlive && ($this->countMessagesConsumed < $this->target || 0 == $this->target));
    }

    /**
     * @param AMQPEnvelope $message
     * @param AMQPQueue $queue
     * @return bool|null
     */
    public function handleDelivery(AMQPEnvelope $message, AMQPQueue $queue)
    {
        $params = compact('message', 'queue');
        $results = $this->getEventManager()->trigger('delivery', $this, $params);
        return $results->last();
    }

    /**
     * Handle shutdown signal
     *
     * @return void
     */
    public function handleShutdownSignal()
    {
        $this->keepAlive = false;
    }

    /**
     * Handle delivery exception
     *
     * @param \Exception $e
     * @return void
     */
    public function handleDeliveryException(\Exception $e)
    {
        $params = ['exception' => $e];
        $this->getEventManager()->trigger('deliveryException', $this, $params);
    }

    /**
     * Handle flush deferred exception
     *
     * @param \Exception $e
     * @return void
     */
    public function handleFlushDeferredException(\Exception $e)
    {
        $params = ['exception' => $e];
        $this->getEventManager()->trigger('flushDeferredException', $this, $params);
    }

    /**
     * Process buffered (unacked) messages
     *
     * Messages are deferred until the block size (see prefetch_count) or the timeout is reached
     * The unacked messages will also be flushed immediately when the handleDelivery method returns true
     *
     * @return bool
     */
    public function flushDeferred()
    {
        try {
            $results = $this->getEventManager()->trigger('flushDeferred', $this);
            $result = $results->last();
        } catch (\Exception $e) {
            $result = false;
            $this->handleFlushDeferredException($e);
        }

        return $result;
    }

    /**
     * Fetch the next queue
     *
     * @return AMQPQueue
     */
    protected function fetchNextQueue()
    {
        $this->queues->next();
        return $this->queues->current();
    }

    /**
     * Handle process flag
     *
     * @param AMQPEnvelope $message
     * @param $flag
     * @return void
     */
    protected function handleProcessFlag(AMQPEnvelope $message, $flag)
    {
        if ($flag === self::MSG_REJECT || false === $flag) {
            $this->ackOrNackBlock();
            $this->getQueue()->reject($message->getDeliveryTag(), AMQP_NOPARAM);
        } elseif ($flag === self::MSG_REJECT_REQUEUE) {
            $this->ackOrNackBlock();
            $this->getQueue()->reject($message->getDeliveryTag(), AMQP_REQUEUE);
        } elseif ($flag === self::MSG_ACK || true === $flag) {
            $this->countMessagesConsumed++;
            $this->countMessagesUnacked++;
            $this->lastDeliveryTag = $message->getDeliveryTag();
            $this->timestampLastMessage = microtime(1);
            $this->ack();
        } else { // $flag === self::MSG_DEFER || null === $flag
            $this->countMessagesConsumed++;
            $this->countMessagesUnacked++;
            $this->lastDeliveryTag = $message->getDeliveryTag();
            $this->timestampLastMessage = microtime(1);
            if ($message->isRedelivery()) {
                $this->redeliverySeen = true;
            }

        }
    }

    /**
     * Ack all deferred messages
     *
     * This will be called every time the block size (see prefetch_count) or timeout is reached
     *
     * @return void
     */
    protected function ack()
    {
        $this->getQueue()->ack($this->lastDeliveryTag, AMQP_MULTIPLE);
        $this->lastDeliveryTag = null;

        $params = [
            'timestampLastMessage' => $this->timestampLastMessage,
            'timestampLastAck' => $this->timestampLastAck,
            'countMessagesUnacked' => $this->countMessagesUnacked
        ];
        $this->getEventManager()->trigger(__FUNCTION__, $this, $params);

        $this->timestampLastAck = microtime(1);
        $this->countMessagesUnacked = 0;
    }

    /**
     * Send nack for all deferred messages
     *
     * @param bool $requeue
     * @return void
     */
    protected function nackAll($requeue = false)
    {
        $flags = AMQP_MULTIPLE;
        if ($requeue) {
            $flags |= AMQP_REQUEUE;
        }
        $this->getQueue()->nack($this->lastDeliveryTag, $flags);
    }

    /**
     * Handle deferred acks
     *
     * @return void
     */
    protected function ackOrNackBlock()
    {
        if (! $this->lastDeliveryTag) {
            return;
        }

        try {
            $deferredFlushResult = $this->flushDeferred();
        } catch (\Exception $e) {
            $deferredFlushResult = false;
        }

        if (true === $deferredFlushResult) {
            $this->ack();
        } else {
            $this->nackAll(!$this->redeliverySeen);
            $this->lastDeliveryTag = null;
        }
        $this->redeliverySeen = false;
        $this->countMessagesUnacked = 0;
    }
}
