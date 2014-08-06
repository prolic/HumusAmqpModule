<?php

namespace HumusAmqpModule\PluginManager;

use HumusAmqpModule\Exception;
use HumusAmqpModule\Amqp\ConsumerInterface;
use Zend\ServiceManager\AbstractPluginManager;

class AnonConsumer extends AbstractPluginManager
{
    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof ConsumerInterface && $plugin->getQueueOptions()->getName() === null) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s and the queue-name must be null',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            'HumusAmqpModule\Amqp\ConsumerInterface'
        ));
    }
}
