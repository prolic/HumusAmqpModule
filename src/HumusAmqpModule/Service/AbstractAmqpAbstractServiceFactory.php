<?php

namespace HumusAmqpModule\Service;

use Traversable;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConnectionAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string Top-level configuration key indicating amqp configuration
     */
    protected $configKey = 'humus_amqp_module';

    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'connections';

    /**
     * @var array
     */
    protected $instances = array();

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (isset($this->instances[$requestedName])) {
            return true;
        }

        $config = $this->getConfig($serviceLocator);
        if (empty($config)) {
            return false;
        }

        if (isset($config[$this->subConfigKey])
            && (is_array($config[$this->subConfigKey]) || $config[$this->subConfigKey] instanceof Traversable)
            && !empty($config[$this->subConfigKey])
        ) {
            return true;
        }

        return false;
    }

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

        /* @var $serviceLocator \Zend\ServiceManager\ServiceManager */
        $config  = $this->getConfig($serviceLocator);

        $spec = $config[$this->subConfigKey];

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

    /**
     * Get amqp configuration, if any
     *
     * @param  ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$services->has('Config')) {
            $this->config = array();
            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config[$this->configKey])
            || !is_array($config[$this->configKey])
        ) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }
}
