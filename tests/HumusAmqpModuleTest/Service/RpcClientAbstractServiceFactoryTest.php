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

namespace HumusAmqpModuleTest\Service;

use HumusAmqpModule\PluginManager\Connection as ConnectionPluginManager;
use HumusAmqpModule\PluginManager\RpcClient as RpcClientPluginManager;
use HumusAmqpModule\Service\ConnectionAbstractServiceFactory;
use HumusAmqpModule\Service\RpcClientAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

class RpcClientAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var RpcClientAbstractServiceFactory
     */
    protected $components;

    public function setUp()
    {
        $config = array(
            'humus_amqp_module' => array(
                'classes' => array(
                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
                    'rpc_client' => 'HumusAmqpModule\Amqp\RpcClient',
                ),
                'connections' => array(
                    'default' => array(
                        'host' => 'localhost',
                        'port' => 5672,
                        'user' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                        'lazy' => true
                    )
                ),
                'rpc_clients' => array(
                    'test-rpc-client' => array(
                        'expect_serialized_response' => true
                    )
                )
            )
        );

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $dependentComponent = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $cm = new ConnectionPluginManager());
        $cm->addAbstractFactory($dependentComponent);
        $cm->setServiceLocator($services);

        $components = $this->components = new RpcClientAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\RpcClient', $rpccm = new RpcClientPluginManager());
        $rpccm->addAbstractFactory($components);
        $rpccm->setServiceLocator($services);
    }

    public function testCreateRpcClient()
    {
        $rpcClient = $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
        $this->assertInstanceOf('HumusAmqpModule\Amqp\RpcClient', $rpcClient);
        /* @var $rpcClient \HumusAmqpModule\Amqp\RpcClient */
        $this->assertEquals('direct', $rpcClient->getExchangeOptions()->getType());
    }

    public function testCreateRpcClientWithCustomClass()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['rpc_clients']['test-rpc-client']['class'] = __NAMESPACE__
            . '\TestAsset\CustomRpcClient';
        $this->services->setService('Config', $config);

        $rpcClient = $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
        $this->assertInstanceOf(__NAMESPACE__ . '\TestAsset\CustomRpcClient', $rpcClient);
        /* @var $rpcClient \HumusAmqpModule\Amqp\RpcClient */
        $this->assertEquals('direct', $rpcClient->getExchangeOptions()->getType());
    }
}
