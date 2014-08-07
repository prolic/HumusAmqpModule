<?php

namespace HumusAmqpModule\Service\Controller;

class MultipleConsumerFactory extends AbstractConsumerFactory
{
    /**
     * @return string
     */
    protected function getConsumerType()
    {
        return 'HumusAmqpModule\\PluginManager\\MultipleConsumer';
    }

    /**
     * @return string
     */
    protected function getControllerClass()
    {
        return 'HumusAmqpModule\Controller\ConsumerController';
    }
}
