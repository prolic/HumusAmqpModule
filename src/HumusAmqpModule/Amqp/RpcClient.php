<?php

namespace HumusAmqpModule\Amqp;

use PhpAmqpLib\Message\AMQPMessage;

class RpcClient extends AbstractAmqp
{
    /**
     * @var int
     */
    protected $requests = 0;

    /**
     * @var array
     */
    protected $replies = array();

    /**
     * @var bool
     */
    protected $expectSerializedResponse;

    /**
     * @var int
     */
    protected $timeout = 0;

    private $queueName;

    public function initClient($expectSerializedResponse = true)
    {
        $this->expectSerializedResponse = $expectSerializedResponse;
    }

    public function addRequest($msgBody, $server, $requestId = null, $routingKey = '', $expiration = 0)
    {
        if (empty($requestId)) {
            throw new Exception\InvalidArgumentException('You must provide a $requestId');
        }

        $msg = new AMQPMessage($msgBody, array('content_type' => 'text/plain',
            'reply_to' => $this->getQueueName(),
            'delivery_mode' => 1, // non durable
            'expiration' => $expiration*1000,
            'correlation_id' => $requestId));

        $this->getChannel()->basic_publish($msg, $server, $routingKey);

        $this->requests++;

        if ($expiration > $this->timeout) {
            $this->timeout = $expiration;
        }
    }

    public function getReplies()
    {
        $this->replies = array();
        $this->getChannel()->basic_consume($this->getQueueName(), '', false, true, false, false, array($this, 'processMessage'));

        while (count($this->replies) < $this->requests) {
            $this->getChannel()->wait(null, false, $this->timeout);
        }

        $this->getChannel()->basic_cancel($this->getQueueName());
        $this->requests = 0;
        $this->timeout = 0;

        return $this->replies;
    }

    public function processMessage(AMQPMessage $msg)
    {
        $messageBody = $msg->body;
        if ($this->expectSerializedResponse) {
            $messageBody = unserialize($messageBody);
        }

        $this->replies[$msg->get('correlation_id')] = $messageBody;
    }

    protected function getQueueName()
    {
        if (null === $this->queueName) {
            list($this->queueName, ,) = $this->getChannel()->queue_declare("", false, false, true, true);
        }

        return $this->queueName;
    }
}
