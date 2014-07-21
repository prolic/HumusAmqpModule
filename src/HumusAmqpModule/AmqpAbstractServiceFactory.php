<?php

namespace HumusAmqpModule;

use ArrayAccess;
use Traversable;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;

class AmqpAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string Top-level configuration key indicating forms configuration
     */
    protected $configKey     = 'humus_amqp_module';

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
        $config = $this->getConfig($serviceLocator);
        if (empty($config)) {
            return false;
        }

        foreach ($config as $amqpType => $data) {
            foreach ($data as $amqpName => $spec) {

                // default connection gets a namespace prefix
                if ($amqpType == 'connections' && $amqpName == 'default') {
                    $amqpName = __NAMESPACE__ . '\\default';
                }

                // found, return true
                if ($amqpName == $name) {
                    return true;
                }
            }
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
        $config  = $this->getConfig($serviceLocator);

        $amqpType = '';
        $amqpName = '';
        $spec = array();
        foreach ($config as $amqpType => $data) {
            foreach ($data as $amqpName => $spec) {
                if ($amqpName == $name) {
                    break 2;
                }
            }
        }

        // default connection gets a namespace prefix
        if ($amqpType == 'connections' && $amqpName == 'default') {
            $amqpName = __NAMESPACE__ . '\\default';
        }

        $method = 'create' . ucfirst(substr($amqpType, 0, -1));
        return $this->{$method}($serviceLocator, $spec);
    }

    protected function createConnection(ServiceLocatorInterface $serviceLocator, $spec)
    {
        $config = $this->config;

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

        return $connection;
    }

    protected function createProducer(ServiceLocatorInterface $serviceLocator, $spec)
    {
        $config = $this->config;

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else {
            $class = $config['classes']['producer'];
        }

        if (!isset($spec['connection'])) {
            $spec['connection'] = 'default';
        }
        if ($spec['connection'] == 'default') {
            $spec['connection'] = __NAMESPACE__ . '\\default';
        }

        $connection = $serviceLocator->get($spec['connection']);
        /** @var  $producer \HumusAmqpModule\Amqp\Producer */
        $producer = new $class($connection);

        if (isset($options['exchange_options'])) {
            $producer->setExchangeOptions($options['exchange_options']);
        }

        if (isset($options['queue_options'])) {
            $producer->setQueueOptions($options['queue_options']);
        }

        if (isset($options['auto_setup_fabric']) && !$options['auto_setup_fabric']) {
            $producer->disableAutoSetupFabric();
        }

        return $producer;
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

    /**
     * Validate a provided specification
     *
     * Ensures we have an array, Traversable, or ArrayAccess object, and returns it.
     *
     * @param  array|Traversable|ArrayAccess $spec
     * @param  string $method Method invoking the validator
     * @return array|ArrayAccess
     * @throws Exception\InvalidArgumentException for invalid $spec
     */
    protected function validateSpecification($spec, $method)
    {
        if (is_array($spec)) {
            return $spec;
        }

        if ($spec instanceof Traversable) {
            $spec = ArrayUtils::iteratorToArray($spec);
            return $spec;
        }

        if (!$spec instanceof ArrayAccess) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array, or object implementing Traversable or ArrayAccess; received "%s"',
                $method,
                (is_object($spec) ? get_class($spec) : gettype($spec))
            ));
        }

        return $spec;
    }
}
