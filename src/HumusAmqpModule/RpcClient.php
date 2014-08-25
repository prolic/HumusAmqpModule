<?php

namespace HumusAmqpModule;

use AMQPExchange;
use AMQPQueue;

class RpcClient
{
    /**
     * @var AMQPQueue
     */
    protected $queue;

    /**
     * @var int
     */
    protected $requests;

    /**
     * @var array
     */
    protected $replies = array();

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

        $messageAttributes = new MessageAttributes();
        $messageAttributes->setReplyTo($this->queue->getName());
        $messageAttributes->setDeliveryMode(1);
        $messageAttributes->setCorrelationId($requestId);
        if (0 != $expiration) {
            $messageAttributes->setExpiration($expiration * 1000);
        }

        $exchange = $this->getExchange($server);
        $exchange->publish($msgBody, $routingKey, $messageAttributes->getFlags(), $messageAttributes->toArray());
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
        $this->replies = array();
        do {
            $message = $this->queue->get(AMQP_AUTOACK);

            if ($message) {
                $this->replies[$message->getCorrelationId()] = $message->getBody();
            } else {
                usleep(1000); // 1/1000 sec
            }

            $time = microtime(1);
        } while (
            (count($this->replies) < $this->requests)
            || (($time - $now) < $this->timeout)
        );

        $this->requests = 0;
        $this->timeout = 0;

        return $this->replies;
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
