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

use AMQPExchange;
use AMQPQueue;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

class RpcClient implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    /**
     * @var AMQPQueue
     */
    protected $queue;

    /**
     * @var int
     */
    protected $requests;

    /**
     * @var int
     */
    protected $timeout = 0;

    /**
     * @var AMQPExchange[]
     */
    protected $exchanges = array();

    /**
     * Constructor
     *
     * @param AMQPQueue $queue
     */
    public function __construct(AMQPQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Add a request to rpc client
     *
     * @param string $msgBody
     * @param string $server
     * @param string $requestId
     * @param string $routingKey
     * @param int $expiration
     * @throws Exception\InvalidArgumentException
     */
    public function addRequest($msgBody, $server, $requestId, $routingKey = '', $expiration = 0)
    {
        if (empty($requestId)) {
            throw new Exception\InvalidArgumentException('You must provide a request Id');
        }

        $argv = compact('msgBody', 'server', 'requestId', 'routingKey', 'expiration');
        $argv = $this->getEventManager()->prepareArgs($argv);
        $this->getEventManager()->trigger(__FUNCTION__, $this, $argv);

        $messageAttributes = new MessageAttributes();
        $messageAttributes->setReplyTo($this->queue->getName());
        $messageAttributes->setDeliveryMode(MessageAttributes::DELIVERY_MODE_NON_PERSISTENT);
        $messageAttributes->setCorrelationId($argv['requestId']);

        $expiration = $argv['expiration'];

        if (0 != $expiration) {
            $messageAttributes->setExpiration($expiration * 1000);
        }

        $exchange = $this->getExchange($argv['server']);
        $exchange->publish($argv['msgBody'], $argv['routingKey'], $messageAttributes->getFlags(), $messageAttributes->toArray());
        $this->requests++;

        if ($expiration > $this->timeout) {
            $this->timeout = $expiration;
        }
    }

    /**
     * Get rpc client replies
     *
     * Example:
     *
     * array(
     *     'message_id_1' => 'foo',
     *     'message_id_2' => 'bar'
     * )
     *
     * @return array
     */
    public function getReplies()
    {
        $now = microtime(1);
        $replies = [];
        do {
            $message = $this->queue->get(AMQP_AUTOACK);

            if ($message) {
                $replies[$message->getCorrelationId()] = $message->getBody();
            } else {
                usleep(1000); // 1/1000 sec
            }

            $time = microtime(1);
        } while (
            (count($replies) < $this->requests)
            || (($time - $now) < $this->timeout)
        );

        $this->requests = 0;
        $this->timeout = 0;

        $argv = $this->getEventManager()->prepareArgs(['replies' => $replies]);
        $this->getEventManager()->trigger(__FUNCTION__, $this, $argv);

        return $argv['replies'];
    }

    /**
     * @param string $name
     * @return AMQPExchange
     */
    protected function getExchange($name)
    {
        if (isset($this->exchanges[$name])) {
            return $this->exchanges[$name];
        }

        $channel = $this->queue->getChannel();
        $exchange = new AMQPExchange($channel);
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setName($name);
        $this->exchanges[$name] = $exchange;
        return $exchange;
    }
}
