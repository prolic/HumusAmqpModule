<?php

namespace HumusAmqpModule\Service;

use HumusAmqpModule\Exception;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractAmqpCallbackAwareAbstractServiceFactory extends AbstractAmqpConnectionAwareAbstractServiceFactory
{
    /**
     * @var \HumusAmqpModule\PluginManager\Callback
     */
    protected $callbackManager;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \HumusAmqpModule\PluginManager\Callback
     * @throws Exception\RuntimeException
     */
    protected function getCallbackManager(ServiceLocatorInterface $serviceLocator)
    {
        if (null !== $this->callbackManager) {
            return $this->callbackManager;
        }

        if (!$serviceLocator->has('HumusAmqpModule\PluginManager\Callback')) {
            throw new Exception\RuntimeException(
                'HumusAmqpModule\PluginManager\Callback not found'
            );
        }

        $this->callbackManager = $serviceLocator->get('HumusAmqpModule\PluginManager\Callback');
        return $this->callbackManager;
    }
}
