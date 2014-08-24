<?php

namespace HumusAmqpModule;

use AMQPEnvelope;
use AMQPExchange;
use AMQPQueue;

abstract class AbstractRpcServer extends AbstractConsumer
{
    /**
     * @var AMQPExchange
     */
    protected $exchange;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * Constructor
     *
     * @param AMQPExchange $exchange
     * @param array|\Traversable $queues
     * @param float $idleTimeout in seconds
     * @throws Exception\ExtensionNotLoadedException
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(AMQPExchange $exchange, $queues, $idleTimeout = 5.00)
    {
        if (count($queues) != 1) {
            throw new Exception\InvalidArgumentException(
                'RpcServer needs exactly 1 queue, ' . count($queues) . ' given'
            );
        }
        parent::__construct($queues, $idleTimeout);
        $this->exchange = $exchange;
    }

    /**
     * @param AMQPEnvelope $message
     * @param AMQPQueue $queue
     * @return bool|null
     */
    public function handleDelivery(AMQPEnvelope $message, AMQPQueue $queue)
    {
        try {
            $this->countMessagesConsumed++;
            $this->countMessagesUnacked++;
            $this->lastDeliveryTag = $message->getDeliveryTag();
            $this->timestampLastMessage = microtime(1);
            $this->ack();

            $result = $this->processMessage($message, $queue);
            $result = json_encode(array('success' => true, 'result' => $result));
            $this->sendReply($result, $message->getReplyTo(), $message->getCorrelationId());
        } catch (\Exception $e) {
            $result = json_encode(array('success' => false, 'error' => $e->getMessage()));
            $this->sendReply($result, $message->getReplyTo(), $message->getCorrelationId());
        }
    }

    abstract public function processMessage(AMQPEnvelope $message, AMQPQueue $queue);

    /**
     * Send reply to rpc client
     *
     * @param string $body
     * @param string $client
     * @param string $correlationId
     */
    protected function sendReply($body, $client, $correlationId)
    {
        $messageAttributes = new MessageAttributes();
        $messageAttributes->setCorrelationId($correlationId);

        $this->exchange->publish($body, $client, AMQP_NOPARAM, $messageAttributes->toArray());
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
        // ignore, do nothing, message was already acked
    }
}
