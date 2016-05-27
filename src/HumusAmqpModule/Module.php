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

use AMQPConnection;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class Module
 * @package HumusAmqpModule
 */
class Module implements
    AutoloaderProviderInterface,
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ConsoleUsageProviderInterface
{
    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {
        /* @var $e \Zend\Mvc\MvcEvent */
        $serviceManager = $e->getApplication()->getServiceManager();
        /* @var $serviceManager \Zend\ServiceManager\ServiceManager */

        $config = $serviceManager->get('config');

        // Use naming conventions to set up a bunch of services based on namespace:
        $namespaces = [
            PluginManager\Callback::class => 'callback',
            PluginManager\Connection::class => 'connection',
            PluginManager\Producer::class => 'producer',
            PluginManager\Consumer::class => 'consumer',
            PluginManager\RpcClient::class => 'rpc_client',
            PluginManager\RpcServer::class => 'rpc_server',
        ];

        // register plugin managers
        foreach ($namespaces as $serviceName => $configKey) {
            $factory = function () use ($config, $serviceName, $configKey, $serviceManager) {
                $serviceConfig = $config['humus_amqp_module']['plugin_managers'][$configKey];
                /** @var AbstractPluginManager $service */
                $service = new $serviceName($serviceManager, $serviceConfig);
                if (PluginManager\Connection::class === $serviceName) {
                    $service->addInitializer(function (AMQPConnection $connection) {
                        if (isset($connection->persistent) && true === $connection->persistent) {
                            $connection->pconnect();
                            unset($connection->persistent);
                        } else {
                            $connection->connect();
                        }
                    });
                }
                return $service;
            };
            $serviceManager->setFactory($serviceName, $factory);
        }
    }

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
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/../../autoload_classmap.php'
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__,
                ]
            ]
        ];
    }

    /**
     * Get console usage
     *
     * @param ConsoleAdapter $adapter
     * @return array
     */
    public function getConsoleUsage(ConsoleAdapter $adapter)
    {
        $usage = [];

        if (class_exists('HumusSupervisorModule\\Module')) {
            $usage['humus amqp gen-supervisord-config [<path>]'] =
                'Generate supervisord configuration with optional path (absolute or relative)';
        }


        // Describe expected parameters
        $usage['humus amqp list <type>'] = 'List all available types, possible types are: ' . "\n"
            . 'consumers, producers, rpc_clients, rpc_servers, connections';

        $usage['humus amqp setup-fabric'] = 'Setting up the Rabbit MQ fabric';

        $usage['humus amqp list-exchanges'] = 'List all available exchanges';

        $usage['humus amqp consumer <name> [<amount>] [--without-signals|-w]'] =
            'Start a consumer by name, msg limits the messages of available';

        $usage[] = [
            '    Available arguments:'
        ];
        $usage[] = [
            '    --route|-r',
            '    Routing key to use',
        ];
        $usage[] = [
            '    --memory_limit|-l',
            '    Memory limit',
        ];
        $usage[] = [
            '    --debug|-d',
            '    Protocol level debug',
            ''
        ];
        $usage['humus amqp stdin-producer <name> [--route] <msg>'] = 'Produce a with a consumer by bame';

        $usage[] = [
            '    Available arguments:'
        ];
        $usage[] = [
            '    --route|-r',
            '    Routing key to use',
            ''
        ];
        $usage['humus amqp purge-consumer <consumer-name>'] = 'Purge a consumer queue';

        $usage['humus amqp rpc-server <name> [<amount>] [--without-signals|-w]'] = 'Start an rpc server by name';

        return $usage;
    }
}
