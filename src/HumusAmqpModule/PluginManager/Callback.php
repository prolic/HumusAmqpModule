<?php

namespace HumusAmqpModule\PluginManager;

use HumusAmqpModule\Exception;
use PhpAmqpLib\Connection\AbstractConnection;
use Zend\ServiceManager\AbstractPluginManager;

class Callback extends AbstractPluginManager
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
        if (is_callable($plugin)) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must be a callable',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));

    }
}
