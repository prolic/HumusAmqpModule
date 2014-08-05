<?php

namespace HumusAmqpModule\Service;

use HumusAmqpModule\Amqp\Producer;
use HumusAmqpModule\Exception;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProducerAbstractServiceFactory extends AbstractAmqpAbstractServiceFactory
{
    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'producers';

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        // get global service locator, if we are in a plugin manager
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        if (!$serviceLocator->has('HumusAmqpModule\PluginManager\Connection')) {
            throw new Exception\RuntimeException(
                'HumusAmqpModule\PluginManager\Connection not found'
            );
        }

        $config  = $this->getConfig($serviceLocator);

        $spec = $config[$this->subConfigKey][$requestedName];

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else {
            $class = $config['classes']['producer'];
        }

        // use default connection if nothing else present
        if (!isset($spec['connection'])) {
            $spec['connection'] = 'default';
        }

        $connectionManager = $serviceLocator->get('HumusAmqpModule\PluginManager\Connection');
        $connection = $connectionManager->get($spec['connection']);

        $producer = new $class($connection);

        if (!$producer instanceof Producer) {
            throw new Exception\RuntimeException(sprintf(
                'Producer of type %s is invalid; must implement %s',
                (is_object($producer) ? get_class($producer) : gettype($producer)),
                'HumusAmqpModule\Amqp\Producer'
            ));
        }

        if (isset($spec['exchange_options'])) {
            $producer->setExchangeOptions($spec['exchange_options']);
        }

        if (isset($spec['queue_options'])) {
            $producer->setQueueOptions($spec['queue_options']);
        }

        if (isset($spec['auto_setup_fabric']) && !$spec['auto_setup_fabric']) {
            $producer->disableAutoSetupFabric();
        }

        return $producer;
    }
}
