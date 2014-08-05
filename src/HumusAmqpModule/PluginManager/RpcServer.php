<?php

namespace HumusAmqpModule\PluginManager;

use HumusAmqpModule\Exception;
use HumusAmqpModule\Amqp\RpcServer as AmqpRpcServer;
use Zend\ServiceManager\AbstractPluginManager;

class RpcServer extends AbstractPluginManager
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
        if ($plugin instanceof AmqpRpcServer) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            'HumusAmqpModule\Amqp\RpcServer'
        ));
    }
}
