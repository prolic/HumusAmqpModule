<?php

namespace HumusAmqpModule\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

class ConnectionAbstractServiceFactory extends AbstractAmqpAbstractServiceFactory
{
    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'connections';

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
        if (isset($this->instances[$requestedName])) {
            return $this->instances[$requestedName];
        }

        $config  = $this->getConfig($serviceLocator);

        $spec = $config[$this->subConfigKey][$requestedName];

        if (!isset($spec['lazy']) || true == $spec['lazy']) {
            $class = $config['classes']['lazy_connection'];
        } else {
            $class = $config['classes']['connection'];
        }

        $connection = new $class(
            $spec['host'],
            $spec['port'],
            $spec['user'],
            $spec['password'],
            $spec['vhost']
        );

        $this->instances[$requestedName] = $connection;
        return $connection;
    }
}
