<?php

namespace HumusAmqpModule\Service\Controller;

class PurgeAnonConsumerFactory extends AbstractConsumerFactory
{
    /**
     * @return string
     */
    protected function getConsumerType()
    {
        return 'HumusAmqpModule\PluginManager\AnonConsumer';
    }

    /**
     * @return string
     */
    protected function getControllerClass()
    {
        return 'HumusAmqpModule\Controller\PurgeConsumerController';
    }
}
