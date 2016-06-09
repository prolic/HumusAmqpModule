<?php

namespace HumusAmqpModule;

use AMQPEnvelope;
use AMQPQueue;

/**
 * Interface FlushDeferredCallbackInterface
 */
interface FlushDeferredCallbackInterface
{
    /**
     * @param ConsumerInterface $consumer
     * @return mixed
     */
    public function onFlushDeferred(ConsumerInterface $consumer);
}
