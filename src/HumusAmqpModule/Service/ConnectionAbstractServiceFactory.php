<?php

namespace HumusAmqpModule\Service;

use HumusAmqpModule\Exception;
use PhpAmqpLib\Connection\AbstractConnection;
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

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else if (!isset($spec['lazy']) || true == $spec['lazy']) {
            $class = $config['classes']['lazy_connection'];
        } else {
            $class = $config['classes']['connection'];
        }

        if (!class_exists($class)) {
            throw new Exception\RuntimeException(
                'Class "' . $class . '" not found'
            );
        }

        $connection = new $class(
            $spec['host'],
            $spec['port'],
            $spec['user'],
            $spec['password'],
            $spec['vhost']
        );

        if (!$connection instanceof AbstractConnection) {
            throw new Exception\RuntimeException(sprintf(
                'Producer of type %s is invalid; must implement %s',
                (is_object($connection) ? get_class($connection) : gettype($connection)),
                'PhpAmqpLib\Connection\AbstractConnection'
            ));
        }

        $this->instances[$requestedName] = $connection;

        return $connection;
    }
}
