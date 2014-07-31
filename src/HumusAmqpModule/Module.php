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
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;

class Module implements
    AutoloaderProviderInterface,
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
            'humus amqp command'    => '',

            'Available commands:',

            // Describe expected parameters
            array(
                'list <type>',
                'List all available types, possible types are: ' . "\n"
                . 'consumers, multiple_consumers, anon_consumers, producers, rpc_clients, rpc_servers, connections'
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
                'consumer <name> [<amount>] [arguments]',
                'Start a consumer by name, msg limits the messages of available'
            ),
            '    Available arguments:',
            array(
                '    --route|-r',
                '    Routing key to use',
            ),
            array(
                '    --memory_limit|-l',
                '    Memory limit',
            ),
            array(
                '    --without-signals|-w',
                '    Without signals',
            ),
            array(
                '    --debug|-d',
                '    Protocol level debug',
                ''
            ),
            array(
                'stdin-producer <name> [--route] <msg>',
                'Produce a with a consumer by bame'
            ),
            '    Available arguments:',
            array(
                '    --route|-r',
                '    Routing key to use',
            ),
            array(
                'purge <consumer-name>',
                'Purge a queue'
            ),
            array(
                'rpc-server <name> [<amount>] [--debug|-d]',
                'Start an rpc server by name'
            ),
        );
    }
}
