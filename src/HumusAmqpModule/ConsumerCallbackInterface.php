<?php

namespace HumusAmqpModule;

use AMQPEnvelope;
use AMQPQueue;

/**
 * Interface ConsumerCallbackInterface
 */
interface ConsumerCallbackInterface
{
    /**
     * @param AMQPEnvelope      $envelope
     * @param AMQPQueue         $queue
     * @param ConsumerInterface $consumer
     * @return mixed
     */
    public function onMessage(AMQPEnvelope $envelope, AMQPQueue $queue, ConsumerInterface $consumer);
}
