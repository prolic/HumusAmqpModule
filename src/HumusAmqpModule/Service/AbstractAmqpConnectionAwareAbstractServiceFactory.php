<?php

namespace HumusAmqpModule\Service;

use HumusAmqpModule\Exception;
use Traversable;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractAmqpConnectionAwareAbstractServiceFactory extends AbstractAmqpAbstractServiceFactory
{
    /**
     * @var \HumusAmqpModule\PluginManager\Connection
     */
    protected $connectionManager;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \HumusAmqpModule\PluginManager\Connection
     * @throws Exception\RuntimeException
     */
    protected function getConnectionManager(ServiceLocatorInterface $serviceLocator)
    {
        if (null !== $this->connectionManager) {
            return $this->connectionManager;
        }

        if (!$serviceLocator->has('HumusAmqpModule\PluginManager\Connection')) {
            throw new Exception\RuntimeException(
                'HumusAmqpModule\PluginManager\Connection not found'
            );
        }

        $this->connectionManager = $serviceLocator->get('HumusAmqpModule\PluginManager\Connection');
        return $this->connectionManager;
    }
}
