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

namespace HumusAmqpModule;

use HumusAmqpModule\Amqp\QueueOptions;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Zend\EventManager\EventInterface as Event;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ServiceManager\ServiceManager;

class Module implements
    AutoloaderProviderInterface,
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ConsoleUsageProviderInterface
{
    /**
     * Get config
     *
     * @return array|mixed|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Return an array for passing to Zend\Loader\AutoloaderFactory.
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/../../autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * Get console usage
     *
     * @param ConsoleAdapter $adapter
     * @return array
     */
    public function getConsoleUsage(ConsoleAdapter $adapter)
    {
        return array(
            // Describe available commands
            'amqp command'    => '',

            'Available commands:',

            // Describe expected parameters
            array(
                'list <type>',
                'List all available types, possible types are: ' . "\n" . 'consumers, multiple_consumers, anon_consumers, producers, rpc_clients, rpc_servers, connections'
            ),
            array(
                'setup-fabric',
                'Setting up the Rabbit MQ fabric'
            ),
            array(
                'list-exchanges',
                'List all available exchanges'
            ),
            array(
                'supervisor (start|stop|processlist|pid|version|api|islocal)',
                'start/ stop the supervisor, list all processes, get supervisor pid, get supervisor version, get api version'
            ),
            array(
                'consumer <name> [<amount>] [arguments]',
                'Start a consumer by name, msg limits the messages of available'
            ),
            'Available arguments:',
            array(
                '--route|-r',
                'Routing key to use',
            ),
            array(
                '--memory_limit|-l',
                'Memory limit',
            ),
            array(
                '--without-signals|-w',
                'Without signals',
            ),
            array(
                '--debug|-d',
                'Protocol level debug'
            ),
        );
    }

    /**
     * Bootstrap the module / build all connections, producers, consumers,
     * multi consumers, anon consumers, rpc clients and rpc servers
     *
     * @param Event $e
     * @return void
     */
    public function onBootstrap(Event $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();

        $config = $serviceManager->get('Config');
        $moduleConfig = $config['humus_amqp_module'];

        if (isset($moduleConfig['connections'])) {
            $this->buildConnections($serviceManager, $moduleConfig);
        }

        if (isset($moduleConfig['producers'])) {
            $this->buildProducers($serviceManager, $moduleConfig);
        }

        if (isset($moduleConfig['consumers'])) {
            $this->buildConsumers($serviceManager, $moduleConfig);
        }

        if (isset($moduleConfig['multiple_consumers'])) {
            $this->buildMultipleConsumers($serviceManager, $moduleConfig);
        }

        if (isset($moduleConfig['anon_consumers'])) {
            $this->buildAnonConsumers($serviceManager, $moduleConfig);
        }

        if (isset($moduleConfig['rpc_clients'])) {
            $this->buildRpcClients($serviceManager, $moduleConfig);
        }

        if (isset($moduleConfig['rpc_servers'])) {
            $this->buildRpcServers($serviceManager, $moduleConfig);
        }
    }

    /**
     * @param ServiceManager $serviceManager
     * @param array $config
     */
    protected function buildConnections(ServiceManager $serviceManager, array $config)
    {
        foreach ($config['connections'] as $name => $options) {
            $serviceManager->setFactory(__NAMESPACE__ . '\\' . $name, function() use ($config, $options) {

                if (!isset($options['lazy']) || true == $options['lazy']) {
                    $class = $config['classes']['lazy_connection'];
                } else {
                    $class = $config['classes']['connection'];
                }

                $connection = new $class(
                    $options['host'],
                    $options['port'],
                    $options['user'],
                    $options['password'],
                    $options['vhost']
                );

                return $connection;
            });
        }
    }

    /**
     * @param ServiceManager $serviceManager
     * @param array $config
     */
    protected function buildProducers(ServiceManager $serviceManager, array $config)
    {
        foreach ($config['producers'] as $name => $options) {
            $serviceManager->setFactory($name, function(ServiceManager $serviceManager) use ($name, $config, $options) {

                if (isset($options['class'])) {
                    $class = $options['class'];
                } else {
                    $class = $config['classes']['producer'];
                }

                //this producer doesn't define an exchange -> using AMQP Default
                if (!isset($options['exchange_options'])) {
                    $options['exchange_options']['name'] = '';
                    $options['exchange_options']['type'] = 'direct';
                    $options['exchange_options']['passive'] = true;
                    $options['exchange_options']['declare'] = false;
                }

                //this producer doesn't define a queue
                if (!isset($producer['queue_options'])) {
                    $producer['queue_options']['name'] = null;
                }

                $connection = $serviceManager->get(__NAMESPACE__ . '\\' . $options['connection']);
                /** @var  $producer \HumusAmqpModule\Amqp\Producer */
                $producer = new $class($connection);

                $producer->setExchangeOptions($options['exchange_options']);
                $producer->setQueueOptions($options['queue_options']);

                if (isset($options['auto_setup_fabric']) && !$options['auto_setup_fabric']) {
                    $producer->disableAutoSetupFabric();
                }

                return $producer;
            });
        }
    }

    /**
     * @param ServiceManager $serviceManager
     * @param array $config
     */
    protected function buildConsumers(ServiceManager $serviceManager, array $config)
    {
        foreach ($config['consumers'] as $name => $options) {
            $serviceManager->setFactory($name, function(ServiceManager $serviceManager) use ($name, $config, $options) {

                if (isset($options['class'])) {
                    $class = $options['class'];
                } else {
                    $class = $config['classes']['consumer'];
                }

                $connection = $serviceManager->get(__NAMESPACE__ . '\\' . $options['connection']);
                /** @var  $consumer \HumusAmqpModule\Amqp\Consumer */
                $consumer = new $class($connection);

                $consumer->setExchangeOptions($options['exchange_options']);
                $consumer->setQueueOptions($options['queue_options']);
                $consumer->setCallback(array(
                    $serviceManager->get($options['callback']),
                    'execute'
                ));

                if (isset($options['qos_options'])) {
                    $consumer->setQosOptions($options['qos_options']);
                }

                if (isset($options['idle_timeout'])) {
                    $consumer->setIdleTimeout($options['idle_timeout']);
                }

                if (isset($options['auto_setup_fabric']) && !$options['auto_setup_fabric']) {
                    $consumer->disableAutoSetupFabric();
                }

                return $consumer;
            });
            $serviceManager->setShared($name, false);
        }
    }

    /**
     * @param ServiceManager $serviceManager
     * @param array $config
     */
    protected function buildMultipleConsumers(ServiceManager $serviceManager, array $config)
    {
        foreach ($config['multiple_consumers'] as $name => $options) {
            $serviceManager->setFactory($name, function(ServiceManager $serviceManager) use ($name, $config, $options) {
                $queues = array();

                if (isset($options['class'])) {
                    $class = $options['class'];
                } else {
                    $class = $config['classes']['multi_consumer'];
                }

                foreach ($options['queues'] as $queueName => $queueOptions) {
                    $qo = new QueueOptions($queueOptions);
                    $queues[$queueOptions['name']]  = $qo;
                    $queues[$queueOptions['name']]['callback'] = array(
                        $serviceManager->get($queueOptions['callback']),
                        'execute'
                    );
                }

                $connection = $serviceManager->get(__NAMESPACE__ . '\\' . $options['connection']);
                /** @var  $consumer \HumusAmqpModule\Amqp\MultipleConsumer */
                $consumer = new $class($connection);

                $consumer->setExchangeOptions($options['exchange_options']);
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
            });
            $serviceManager->setShared($name, false);
        }
    }

    /**
     * @param ServiceManager $serviceManager
     * @param array $config
     */
    protected function buildAnonConsumers(ServiceManager $serviceManager, array $config)
    {
        foreach ($config['anon_consumers'] as $name => $options) {
            $serviceManager->setFactory($name, function(ServiceManager $serviceManager) use ($name, $config, $options) {

                if (isset($options['class'])) {
                    $class = $options['class'];
                } else {
                    $class = $config['classes']['anon_consumer'];
                }

                $connection = $serviceManager->get(__NAMESPACE__ . '\\' . $options['connection']);
                /** @var  $consumer \HumusAmqpModule\Amqp\AnonConsumer */
                $consumer = new $class($connection);
                $consumer->setExchangeOptions($options['exchange_options']);
                $consumer->setCallback(array(
                    $serviceManager->get($options['callback']),
                    'execute'
                ));

                return $consumer;
            });
            $serviceManager->setShared($name, false);
        }
    }

    /**
     * @param ServiceManager $serviceManager
     * @param array $config
     */
    protected function buildRpcClients(ServiceManager $serviceManager, array $config)
    {
        foreach ($config['rpc_clients'] as $key => $client) {
            $serviceManager->setFactory($key, function(ServiceManager $serviceManager) use ($client, $config) {

                if (isset($options['class'])) {
                    $class = $options['class'];
                } else {
                    $class = $config['classes']['rpc_client'];
                }

                $connection = $serviceManager->get(__NAMESPACE__ . '\\' . $client['connection']);
                $rpcClient = new $class($connection);
                $rpcClient->initClient($client['expect_serialized_response']);

                return $rpcClient;
            });
        }
    }

    /**
     * @param ServiceManager $serviceManager
     * @param array $config
     */
    protected function buildRpcServers(ServiceManager $serviceManager, array $config)
    {
        foreach ($config['rpc_servers'] as $key => $server) {
            $serviceManager->setFactory($key, function(ServiceManager $serviceManager) use ($server, $key, $config) {

                if (isset($options['class'])) {
                    $class = $options['class'];
                } else {
                    $class = $config['classes']['rpc_server'];
                }

                $connection = $serviceManager->get(__NAMESPACE__ . '\\' . $server['connection']);
                $rpcServer = new $class($connection);
                $rpcServer->initServer($key);

                if (isset($server['callback'])) {
                    $rpcServer->setCallback(array(
                        $serviceManager->get($server['callback']),
                        'execute'
                    ));
                }

                if (isset($server['qos_options'])) {
                    $rpcServer->setQosOptions($server['qos_options']);
                }

                return $rpcServer;
            });
            $serviceManager->setShared($key, false);
        }
    }
}
