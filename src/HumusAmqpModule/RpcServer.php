<?php

namespace HumusAmqpModule;

use AMQPEnvelope;
use AMQPExchange;
use AMQPQueue;

class RpcServer extends Consumer
{
    /**
     * @var AMQPExchange
     */
    protected $exchange;

    /**
     * Constructor
     *
     * @param AMQPQueue $queue
     * @param float $idleTimeout in seconds
     * @param int $waitTimeout in microseconds
     */
    public function __construct(AMQPQueue $queue, $idleTimeout = 5.00, $waitTimeout = 1000)
    {
        $queues = array($queue);
        parent::__construct($queues, $idleTimeout, $waitTimeout);
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

            $callback = $this->getDeliveryCallback();
            $result = call_user_func_array($callback, array($message, $queue));

            $reponse = json_encode(array('success' => true, 'result' => $result));
            $this->sendReply($reponse, $message->getReplyTo(), $message->getCorrelationId());
        } catch (\Exception $e) {
            $reponse = json_encode(array('success' => false, 'error' => $e->getMessage()));
            $this->sendReply($reponse, $message->getReplyTo(), $message->getCorrelationId());
        }
    }

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

        $this->getExchange()->publish($body, $client, AMQP_NOPARAM, $messageAttributes->toArray());
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

    /**
     * @return AMQPExchange
     */
    protected function getExchange()
    {
        if (null !== $this->exchange) {
            return $this->exchange;
        }
        $channel = $this->getQueue()->getChannel();
        $exchange = new AMQPExchange($channel);
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $this->exchange = $exchange;
        return $exchange;
    }
}
