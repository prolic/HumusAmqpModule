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

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ServiceManager\AbstractPluginManager;

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

        $config = $serviceManager->get('Config');

        // Use naming conventions to set up a bunch of services based on namespace:
        $namespaces = array(
            'Callback' => 'callbacks',
            'Connection' => 'connections',
            'Producer' => 'producers',
            'Consumer' => 'consumers',
            'MultipleConsumer' => 'mutiple_consumers',
            'AnonConsumer' => 'anon_consumers',
            'RpcClient' => 'rpc_clients',
            'RpcServer' => 'rpc_servers'
        );

        // register plugin managers
        foreach ($namespaces as $ns => $configKey) {
            $serviceName = __NAMESPACE__ . '\\PluginManager\\' . $ns;
            $factory = function () use ($serviceName, $config, $ns, $configKey, $serviceManager) {


                $serviceConfig = isset($config['humus_amqp_module']['plugin_managers'][$configKey])
                    ? $config['humus_amqp_module']['plugin_managers'][$configKey]
                    : array();

                /* @var $service AbstractPluginManager */
                $service = new $serviceName(new \Zend\ServiceManager\Config($serviceConfig));
                // add abstract factory
                if ('Callback' != $ns) { // callbacks are defined in plugin manager configuration
                    $service->addAbstractFactory(__NAMESPACE__ . '\\Service\\' . $ns . 'AbstractServiceFactory');
                }
                $service->setServiceLocator($serviceManager);
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
        $usage = array();
        $usage[] = 'humus amqp [command]';
        $usage[] = 'Available commands:' . "\n";


        if (class_exists('HumusSupervisorModule\\Module')) {
            $usage[] = array(
                'gen-supervisord-config [<path>]',
                'Generate supervisord configuration with optional path (absolute or relative)'
            );
        }


        // Describe expected parameters
        $usage[] = array(
            'list <type>',
            'List all available types, possible types are: ' . "\n"
            . 'consumers, multiple_consumers, anon_consumers, producers, rpc_clients, rpc_servers, connections'
        );
        $usage[] = array(
            'setup-fabric',
            'Setting up the Rabbit MQ fabric'
        );
        $usage[] = array(
            'list-exchanges',
            'List all available exchanges'
        );
        $usage[] = array(
            '[consumer|anon-consumer|multiple-consumer] <name> [<amount>] [arguments]',
            'Start a consumer by name, msg limits the messages of available',
        );
        $usage[] = array(
            '    Available arguments:'
        );
        $usage[] = array(
            '    --route|-r',
            '    Routing key to use',
        );
        $usage[] = array(
            '    --memory_limit|-l',
            '    Memory limit',
        );
        $usage[] = array(
            '    --debug|-d',
            '    Protocol level debug',
            ''
        );
        $usage[] = array(
            'stdin-producer <name> [--route] <msg>',
            'Produce a with a consumer by bame'
        );
        $usage[] = array(
            '    Available arguments:'
        );
        $usage[] = array(
            '    --route|-r',
            '    Routing key to use',
            ''
        );
        $usage[] = array(
            'purge-consumer <consumer-name>',
            'Purge a consumer queue'
        );
        $usage[] = array(
            'purge-anon-consumer <consumer-name>',
            'Purge an anon consumer queue'
        );
        $usage[] = array(
            'purge-multiple-consumer <consumer-name>',
            'Purge a multiple consumer queue'
        );
        $usage[] = array(
            'rpc-server <name> [<amount>] [--debug|-d]',
            'Start an rpc server by name'
        );

        return $usage;
    }
}
