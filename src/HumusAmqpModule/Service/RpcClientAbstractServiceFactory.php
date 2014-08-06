<?php

namespace HumusAmqpModule\Service;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class RpcClientAbstractServiceFactory extends AbstractAmqpConnectionAwareAbstractServiceFactory
{
    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'rpc_clients';

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

        $config = $this->getConfig($serviceLocator);

        $spec = $config[$this->subConfigKey][$requestedName];

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else {
            $class = $config['classes']['rpc_client'];
        }

        // use default connection if nothing else present
        if (!isset($spec['connection'])) {
            $spec['connection'] = 'default';
        }

        $connectionManager = $this->getConnectionManager($serviceLocator);

        $connection = $connectionManager->get($spec['connection']);
        $rpcClient = new $class($connection);

        return $rpcClient;
    }
}
