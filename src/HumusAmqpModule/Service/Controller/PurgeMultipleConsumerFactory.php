<?php

namespace HumusAmqpModule\Service\Controller;

class PurgeMultipleConsumerFactory extends AbstractConsumerFactory
{
    /**
     * @return string
     */
    protected function getConsumerType()
    {
        return 'HumusAmqpModule\PluginManager\MultipleConsumer';
    }

    /**
     * @return string
     */
    protected function getControllerClass()
    {
        return 'HumusAmqpModule\Controller\PurgeConsumerController';
    }
}
