<?php

namespace HumusAmqpModule;

/**
 * Interface ExceptionCallbackInterface
 */
interface ExceptionCallbackInterface
{
    /**
     * @param \Exception        $e
     * @param ConsumerInterface $consumer
     *
     * @return void
     */
    public function onDeliveryException(\Exception $e, ConsumerInterface $consumer);

    /**
     * @param \Exception        $e
     * @param ConsumerInterface $consumer
     *
     * @return void
     */
    public function onFlushDeferredException(\Exception $e, ConsumerInterface $consumer);
}
