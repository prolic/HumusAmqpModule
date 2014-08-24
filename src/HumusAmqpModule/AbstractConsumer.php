<?php

namespace HumusAmqpModule;

use AMQPEnvelope;
use AMQPQueue;
use ArrayIterator;
use InfiniteIterator;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerAwareTrait;
use Zend\Log\LoggerInterface;

/**
 * The consumer attaches to a single queue
 *
 * The used block size is the configured prefetch size of the queue's channel
 */
abstract class AbstractConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * The blocksize (see prefetch_count)
     *
     * @var int
     */
    protected $blockSize = 1;

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
     * @throws Exception\ExtensionNotLoadedException
     * @throws Exception\InvalidArgumentException
     *
     * @todo: add wait timeout param
     * @todo: add block size (see: flushDeferred()) param
     * @todo: remove pcntl_signal handler and move to controller
     */
    public function __construct($queues, $idleTimeout = 5.00)
    {
        if (!extension_loaded('pcntl') || !function_exists('pcntl_signal')) {
            throw new Exception\ExtensionNotLoadedException(
                'Missing ext/pcntl'
            );
        }

        if (!is_array($queues) || !$queues instanceof \Traversable) {
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
            $q[] = $queue;
            $this->blockSize = max($queue->getChannel()->getPrefetchSize(), $this->blockSize);
        }
        $this->idleTimeout = $idleTimeout;
        $this->queues = new InfiniteIterator(new ArrayIterator($q));

        pcntl_signal(SIGUSR1, array($this, 'handleShutdownSignal'));
        pcntl_signal(SIGINT,  array($this, 'handleShutdownSignal'));
        pcntl_signal(SIGTERM, array($this, 'handleShutdownSignal'));
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
                usleep(1000); // 1/1000 sec
            }

            $now = microtime(1);

            if ($this->countMessagesUnacked == $this->blockSize
                || ($now - $this->timestampLastAck) > $this->idleTimeout
            ) {
                $this->ack();
            }

        } while ($this->keepAlive && ($this->countMessagesConsumed < $this->target || 0 == $this->target));
    }

    /**
     * Handle shutdown signal
     *
     * @return void
     */
    final public function handleShutdownSignal()
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
        $this->getLogger()->err('Exception during handleDelivery: ' . $e->getMessage());
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
        return true;
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
        } else if ($flag === self::MSG_REJECT_REQUEUE) {
            $this->ackOrNackBlock();
            $this->getQueue()->reject($message->getDeliveryTag(), AMQP_REQUEUE);
        } else if ($flag === self::MSG_ACK || true === $flag) {
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
        $delta = $this->timestampLastMessage - $this->timestampLastAck;
        $this->logger->debug(sprintf(
            'Acknowledged %d messages at %.0f msg/s',
            $this->countMessagesUnacked,
            $delta ? $this->countMessagesUnacked / $delta : 0
        ));
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
    protected function ackOrNackBlock ()
    {
        if (! $this->lastDeliveryTag) {
            return;
        }

        try {
            $deferredFlushResult = $this->flushDeferred();
        } catch (\Exception $e) {
            $this->getLogger()->err('Exception during flushDeferred: ' . $e->getMessage());
            $deferredFlushResult = false;
        }

        if (true === $deferredFlushResult) {
            $this->ack();
        } else {
            $this->nackAll(
                $this->lastDeliveryTag,
                ! $this->redeliverySeen
            );
            $this->lastDeliveryTag = null;
        }
        $this->redeliverySeen = false;
        $this->countMessagesUnacked = 0;
    }
}
