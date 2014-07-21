<?php

namespace HumusAmqpModule;

use ArrayAccess;
use HumusAmqpModule\Amqp\QueueOptions;
use HumusAmqpModule\Amqp\RpcClient;
use HumusAmqpModule\Amqp\RpcServer;
use PhpAmqpLib\Connection\AbstractConnection;
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
     * @var string Top-level configuration key indicating amqp configuration
     */
    protected $configKey     = 'humus_amqp_module';

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

        foreach ($config as $amqpType => $data) {
            foreach ($data as $amqpName => $spec) {

                // default connection gets a namespace prefix
                if ($amqpType == 'connections' && $amqpName == 'default') {
                    $amqpName = __NAMESPACE__ . '\\default';
                }

                // found, return true
                if ($amqpName == $requestedName) {
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
        if (isset($this->instances[$requestedName])) {
            return $this->instances[$requestedName];
        }

        /* @var $serviceLocator \Zend\ServiceManager\ServiceManager */
        $config  = $this->getConfig($serviceLocator);

        $amqpType = '';
        $amqpName = '';
        $spec = array();
        foreach ($config as $amqpType => $data) {
            foreach ($data as $amqpName => $spec) {

                // default connection gets a namespace prefix
                if ($amqpType == 'connections' && $amqpName == 'default') {
                    $amqpName = __NAMESPACE__ . '\\default';
                }

                if ($amqpName == $requestedName) {
                    break 2;
                }
            }
        }

        switch ($amqpType) {
            case 'connections':
                return $this->createConnection($serviceLocator, $spec);
                break;
            case 'consumers':
                $instance = $this->createConsumer($serviceLocator, $spec);
                $this->instances[$requestedName] = $instance;
                return $instance;
            case 'producers':
                return $this->createProducer($serviceLocator, $spec);
            case 'anon_consumers':
                $instance = $this->createAnonConsumer($serviceLocator, $spec);
                $this->instances[$requestedName] = $instance;
                return $instance;
            case 'multiple_consumers':
                $instance = $this->createMultipleConsumer($serviceLocator, $spec);
                $this->instances[$requestedName] = $instance;
                return $instance;
            case 'rpc_servers':
                $instance = $this->createRpcServer($serviceLocator, $spec);
                $instance->initServer($requestedName);
                $this->instances[$requestedName] = $instance;
                return $instance;
            case 'rpc_clients':
                return $this->createRpcClient($serviceLocator, $spec);
        }
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array|Traversable $spec
     * @return AbstractConnection
     */
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

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array|Traversable $spec
     * @return Amqp\Producer
     */
    protected function createProducer(ServiceLocatorInterface $serviceLocator, $spec)
    {
        $config = $this->config;

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else {
            $class = $config['classes']['producer'];
        }

        if (!isset($spec['connection']) || $spec['connection'] == 'default') {
            $spec['connection'] = __NAMESPACE__ . '\\default';
        }

        $connection = $serviceLocator->get($spec['connection']);
        /** @var  $producer \HumusAmqpModule\Amqp\Producer */
        $producer = new $class($connection);

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

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array|Traversable $spec
     * @return Amqp\Consumer
     */
    protected function createConsumer(ServiceLocatorInterface $serviceLocator, $spec)
    {
        $config = $this->config;

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else {
            $class = $config['classes']['consumer'];
        }

        if (!isset($spec['connection']) || $spec['connection'] == 'default') {
            $spec['connection'] = __NAMESPACE__ . '\\default';
        }

        $connection = $serviceLocator->get($spec['connection']);
        /** @var  $consumer \HumusAmqpModule\Amqp\Consumer */
        $consumer = new $class($connection);

        $consumer->setExchangeOptions($spec['exchange_options']);
        $consumer->setQueueOptions($spec['queue_options']);
        $consumer->setCallback(array(
            $serviceLocator->get($spec['callback']),
            'execute'
        ));

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
     * @param array|Traversable $spec
     * @return Amqp\MultipleConsumer
     */
    protected function createMultipleConsumer(ServiceLocatorInterface $serviceLocator, $spec)
    {
        $config = $this->config;
        $queues = array();

        foreach ($spec['queues'] as $queueOptions) {
            $qo = new QueueOptions($queueOptions);
            $callback = array(
                $serviceLocator->get($qo->getCallback()),
                'execute'
            );
            $qo->setCallback($callback);
            $queues[$qo->getName()] = $qo;
        }

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else {
            $class = $config['classes']['multi_consumer'];
        }

        if (!isset($spec['connection']) || $spec['connection'] == 'default') {
            $spec['connection'] = __NAMESPACE__ . '\\default';
        }

        $connection = $serviceLocator->get($spec['connection']);
        /* @var  $consumer \HumusAmqpModule\Amqp\MultipleConsumer */
        $consumer = new $class($connection);

        $consumer->setExchangeOptions($spec['exchange_options']);
        $consumer->setQueues($queues);

        if (isset($options['qos_options'])) {
            $consumer->setQosOptions($options['qos_options']);
        }

        if (isset($options['idle_timeout'])) {
            $consumer->setIdleTimeout($options['idle_timeout']);
        }

        if (isset($options['auto_setup_fabric']) && true == $options['auto_setup_fabric']) {
            $consumer->disableAutoSetupFabric();
        }

        return $consumer;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array|Traversable $spec
     * @return Amqp\AnonConsumer
     */
    protected function createAnonConsumer(ServiceLocatorInterface $serviceLocator, $spec)
    {
        $config = $this->config;

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else {
            $class = $config['classes']['anon_consumer'];
        }

        if (!isset($spec['connection']) || $spec['connection'] == 'default') {
            $spec['connection'] = __NAMESPACE__ . '\\default';
        }

        $connection = $serviceLocator->get($spec['connection']);
        /* @var  $consumer \HumusAmqpModule\Amqp\AnonConsumer */
        $consumer = new $class($connection);
        $consumer->setExchangeOptions($spec['exchange_options']);
        $consumer->setCallback(array(
            $serviceLocator->get($spec['callback']),
            'execute'
        ));

        return $consumer;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array|Traversable $spec
     * @return RpcClient
     */
    protected function createRpcClient(ServiceLocatorInterface $serviceLocator, $spec)
    {
        $config = $this->config;

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else {
            $class = $config['classes']['rpc_client'];
        }

        if (!isset($spec['connection']) || $spec['connection'] == 'default') {
            $spec['connection'] = __NAMESPACE__ . '\\default';
        }

        $connection = $serviceLocator->get($spec['connection']);
        $rpcClient = new $class($connection);
        /* @var $rpcClient RpcClient */
        return $rpcClient;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array|Traversable $spec
     * @return Amqp\RpcServer
     */
    protected function createRpcServer(ServiceLocatorInterface $serviceLocator, $spec)
    {
        $config = $this->config;

        if (isset($spec['class'])) {
            $class = $spec['class'];
        } else {
            $class = $config['classes']['rpc_server'];
        }

        if (!isset($spec['connection']) || $spec['connection'] == 'default') {
            $spec['connection'] = __NAMESPACE__ . '\\default';
        }

        $connection = $serviceLocator->get($spec['connection']);
        $rpcServer = new $class($connection);
        /* @var $rpcServer RpcServer */

        if (isset($spec['callback'])) {
            $rpcServer->setCallback(array(
                $serviceLocator->get($spec['callback']),
                'execute'
            ));
        }

        if (isset($spec['qos_options'])) {
            $rpcServer->setQosOptions($spec['qos_options']);
        }

        return $rpcServer;
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
