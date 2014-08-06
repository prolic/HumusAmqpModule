<?php

namespace HumusAmqpModule\Service;

use HumusAmqpModule\Amqp\MultipleConsumerInterface;
use HumusAmqpModule\Amqp\QueueOptions;
use HumusAmqpModule\Exception;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class MultipleConsumerAbstractServiceFactory extends AbstractAmqpAbstractServiceFactory
{
    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'multiple_consumers';

    /**
     * @var \HumusAmqpModule\PluginManager\Connection
     */
    protected $connectionManager;

    /**
     * @var \HumusAmqpModule\PluginManager\Callback
     */
    protected $callbackManager;

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

        $config  = $this->getConfig($serviceLocator);

        $callbackManager = $this->getCallbackManager($serviceLocator);
        $connectionManager = $this->getConnectionManager($serviceLocator);

        $spec = $config[$this->subConfigKey][$requestedName];

        $queues = array();

        foreach ($spec['queues'] as $queueOptions) {
            $qo = new QueueOptions($queueOptions);
            $qo->setCallback($callbackManager->get($qo->getCallback()));
            $queues[$qo->getName()] = $qo;
        }

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else {
            $class = $config['classes']['multiple_consumer'];
        }

        // use default connection if nothing else present
        if (!isset($spec['connection'])) {
            $spec['connection'] = 'default';
        }

        $connection = $connectionManager->get($spec['connection']);
        $consumer = new $class($connection);

        if (!$consumer instanceof MultipleConsumerInterface) {
            throw new Exception\RuntimeException(sprintf(
                'Consumer of type %s is invalid; must implement %s',
                (is_object($consumer) ? get_class($consumer) : gettype($consumer)),
                'HumusAmqpModule\Amqp\MultipleConsumerInterface'
            ));
        }

        $consumer->setExchangeOptions($spec['exchange_options']);
        $consumer->setQueues($queues);

        if (isset($spec['qos_options'])) {
            $consumer->setQosOptions($spec['qos_options']);
        }

        if (isset($spec['idle_timeout'])) {
            $consumer->setIdleTimeout($spec['idle_timeout']);
        }

        if (isset($spec['auto_setup_fabric']) && !$spec['auto_setup_fabric']) {
            $consumer->disableAutoSetupFabric();
        }

        return $consumer;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \HumusAmqpModule\PluginManager\Connection
     * @throws \HumusAmqpModule\Exception\RuntimeException
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

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \HumusAmqpModule\PluginManager\Callback
     * @throws \HumusAmqpModule\Exception\RuntimeException
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
