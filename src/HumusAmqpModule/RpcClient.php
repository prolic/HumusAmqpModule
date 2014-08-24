<?php

namespace HumusAmqpModule;

use AMQPExchange;
use AMQPQueue;

class RpcClient
{
    /**
     * @var AMQPExchange
     */
    protected $exchange;

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
     * Constructor
     *
     * @param AMQPExchange $exchange
     * @param AMQPQueue $queue
     */
    public function __construct(AMQPExchange $exchange, AMQPQueue $queue)
    {
        $this->exchange = $exchange;
        $this->queue = $queue;
    }

    /**
     * Add a request to rpc client
     *
     * @param string $msgBody
     * @param string $requestId
     * @param string $routingKey
     * @param int $expiration
     * @throws Exception\InvalidArgumentException
     */
    public function addRequest($msgBody, $requestId, $routingKey = '', $expiration = 0)
    {
        if (empty($requestId)) {
            throw new Exception\InvalidArgumentException('You must provide a request Id');
        }

        $messageAttributes = new MessageAttributes();
        $messageAttributes->setReplyTo($this->queue->getName());
        $messageAttributes->setDeliveryMode(MessageAttributes::DELIVERY_MODE_PERSISTENT);
        $messageAttributes->setExpiration($expiration * 1000);
        $messageAttributes->setCorrelationId($requestId);

        $this->exchange->publish($msgBody, $routingKey, $messageAttributes->getFlags(), $messageAttributes->toArray());
        $this->requests++;
    }

    /**
     * Get rpc client replies
     *
     * Note: If you set the timeout equals to message timeout, it could happen that the message is
     * not yet delivered back to the rpc client, so the response gets timed out
     *
     * Example:
     *
     * array(
     *     'message_id_1' => 'foo',
     *     'message_id_2' => 'bar'
     * )
     *
     * @param float $timeout in secs
     * @return array
     */
    public function getReplies($timeout)
    {
        $now = microtime(1);
        $this->replies = array();
        do {
            $message = $this->queue->get();
            if ($message) {
                $this->replies[$message->getCorrelationId()] = $message->getBody();
            } else {
                usleep(1000); // 1/1000 sec
            }

            $time = microtime(1);
        } while (
            (count($this->replies) < $this->requests)
            || (($time - $now) < $timeout)
        );

        $this->requests = 0;
        $this->timeout = 0;

        return $this->replies;
    }
}
