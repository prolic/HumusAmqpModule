<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace HumusAmqpModule\Service;

use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use HumusAmqpModule\Exception;
use HumusAmqpModule\ExchangeFactory;
use HumusAmqpModule\ExchangeFactoryInterface;
use HumusAmqpModule\ExchangeSpecification;
use HumusAmqpModule\PluginManager\Connection as ConnectionManager;
use HumusAmqpModule\QosOptions;
use Interop\Container\ContainerInterface;
use Traversable;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AbstractAmqpAbstractServiceFactory
 * @package HumusAmqpModule\Service
 */
abstract class AbstractAmqpAbstractServiceFactory implements AbstractFactoryInterface
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
    protected $subConfigKey = '';

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var string
     */
    protected $defaultConnectionName;

    /**
     * @var array
     */
    protected $specs = [];

    /**
     * @var ExchangeFactoryInterface
     */
    protected $exchangeFactory;

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $this->getConfig($container);
        if (!count($config)) {
            return false;
        }

        if (!isset($config[$this->subConfigKey][$requestedName])) {
            return false;
        }

        $spec = $config[$this->subConfigKey][$requestedName];

        return is_array($spec) || $spec instanceof Traversable;
    }

    /**
     * @param ServiceLocatorInterface $services
     * @param string                  $name
     * @param string                  $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this($services, $requestedName);
    }

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @return mixed
     */
    abstract public function __invoke(ContainerInterface $container, $requestedName, array $options = null);

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->canCreate($serviceLocator, $requestedName);
    }

    /**
     * Get amqp configuration, if any
     *
     * @param  ContainerInterface $container
     * @return array
     */
    protected function getConfig(ContainerInterface $container)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        // get global service locator, if we are in a plugin manager
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator();
        }

        if (!$container->has('config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $container->get('config');
        if (!array_key_exists($this->configKey, $config) || !is_array($config[$this->configKey])) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }

    /**
     * @param ContainerInterface $container
     * @return string
     */
    protected function getDefaultConnectionName(ContainerInterface $container)
    {
        if (null === $this->defaultConnectionName) {
            $config = $this->getConfig($container);
            $this->defaultConnectionName = $config['default_connection'];
        }
        return $this->defaultConnectionName;
    }

    /**
     * @param ContainerInterface $container
     * @return AMQPConnection
     */
    protected function getDefaultConnection(ContainerInterface $container)
    {
        $connectionManager = $this->getConnectionManager($container);
        $connection = $connectionManager->get($this->getDefaultConnectionName($container));

        return $connection;
    }

    /**
     * @param ContainerInterface $container
     * @param array              $spec
     * @return AMQPConnection
     */
    protected function getConnection(ContainerInterface $container, array $spec)
    {
        if (!isset($spec['connection'])) {
            return $this->getDefaultConnection($container);
        }

        $connectionManager = $this->getConnectionManager($container);
        return $connectionManager->get($spec['connection']);
    }

    /**
     * Note: Exchanges are not shared, only using producers or consumers can be shared
     *
     * @param ContainerInterface $services
     * @param AMQPChannel $channel
     * @param string $name
     * @param bool $autoSetupFabric
     * @return AMQPExchange
     */
    protected function getExchange(
        ContainerInterface $services,
        AMQPChannel $channel,
        $name,
        $autoSetupFabric
    ) {
        $exchangeSpec = $this->getExchangeSpec($services, $name);
        $exchange = $this->getExchangeFactory()->create($exchangeSpec, $channel, $autoSetupFabric);

        return $exchange;
    }

    /**
     * @param AMQPConnection $connection
     * @param array $spec
     * @return AMQPChannel
     */
    protected function createChannel(AMQPConnection $connection, array $spec)
    {
        $qosOptions = isset($spec['qos']) ? new QosOptions($spec['qos']) : new QosOptions();

        $channel = new AMQPChannel($connection);
        $channel->setPrefetchSize($qosOptions->getPrefetchSize());
        $channel->setPrefetchCount($qosOptions->getPrefetchCount());

        return $channel;
    }

    /**
     * @return ExchangeFactoryInterface
     */
    public function getExchangeFactory()
    {
        if (null === $this->exchangeFactory) {
            $this->setExchangeFactory(new ExchangeFactory());
        }
        return $this->exchangeFactory;
    }

    /**
     * @param ExchangeFactoryInterface $exchangeFactory
     */
    public function setExchangeFactory(ExchangeFactoryInterface $exchangeFactory)
    {
        $this->exchangeFactory = $exchangeFactory;
    }

    /**
     * @param ContainerInterface $container
     * @param string             $exchangeName
     * @return ExchangeSpecification
     */
    protected function getExchangeSpec(ContainerInterface $container, $exchangeName)
    {
        $config  = $this->getConfig($container);
        return new ExchangeSpecification($config['exchanges'][$exchangeName]);
    }

    /**
     * @param array $spec
     * @return bool
     */
    protected function useAutoSetupFabric(array $spec)
    {
        return array_key_exists('auto_setup_fabric', $spec) && $spec['auto_setup_fabric'];
    }

    /**
     * @param ContainerInterface $container
     * @param string             $name
     * @param string             $requestedName
     * @return array
     */
    protected function getSpec(ContainerInterface $container, $name, $requestedName)
    {
        if (array_key_exists($name, $this->specs)) {
            return $this->specs[$name];
        }

        $config  = $this->getConfig($container);
        $spec = $config[$this->subConfigKey][$requestedName];

        $this->specs[$name] = $spec;

        return $spec;
    }

    /**
     * @param ContainerInterface $container
     * @return ConnectionManager
     * @throws \HumusAmqpModule\Exception\RuntimeException
     */
    protected function getConnectionManager(ContainerInterface $container)
    {
        if (null === $this->connectionManager) {
            if (!$container->has(ConnectionManager::class)) {
                throw new Exception\RuntimeException(sprintf('%s not found', ConnectionManager::class));
            }
            $this->connectionManager = $container->get(ConnectionManager::class);
        }
        return $this->connectionManager;
    }
}
