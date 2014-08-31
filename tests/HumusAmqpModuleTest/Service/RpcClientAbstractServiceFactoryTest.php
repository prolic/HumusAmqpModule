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
use Zend\ServiceManager\ServiceManager;

class RpcClientAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var TestAsset\RpcClientAbstractServiceFactory
     */
    protected $components;

    public function setUp()
    {
        $config = array(
            'humus_amqp_module' => array(
                'default_connection' => 'default',
                'connections' => array(
                    'default' => array(
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                    )
                ),
                'exchanges' => array(
                    'test-rpc-client' => array(
                        'name' => 'test-rpc-client',
                        'type' => 'direct'
                    ),
                ),
                'queues' => array(
                    'test-rpc-client' => array(
                        'name' => '',
                        'exchange' => 'test-rpc-client'
                    ),
                ),
                'rpc_clients' => array(
                    'test-rpc-client' => array(
                        'queue' => 'test-rpc-client'
                    )
                )
            )
        );

        $connection = $this->getMock('AMQPConnection', array(), array(), '', false);
        $channel    = $this->getMock('AMQPChannel', array(), array(), '', false);
        $channel
            ->expects($this->any())
            ->method('getPrefetchCount')
            ->will($this->returnValue(10));
        $queue      = $this->getMock('AMQPQueue', array(), array(), '', false);
        $queue
            ->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));
        $queueFactory = $this->getMock('HumusAmqpModule\QueueFactory');
        $queueFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($queue));

        $connectionManager = $this->getMock('HumusAmqpModule\PluginManager\Connection');
        $connectionManager
            ->expects($this->any())
            ->method('get')
            ->with('default')
            ->willReturn($connection);

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $dependentComponent = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $cm = new ConnectionPluginManager());
        $cm->addAbstractFactory($dependentComponent);
        $cm->setServiceLocator($services);

        $components = $this->components = new TestAsset\RpcClientAbstractServiceFactory();
        $components->setChannelMock($channel);
        $components->setQueueFactory($queueFactory);
        $services->setService('HumusAmqpModule\PluginManager\RpcClient', $rpccm = new RpcClientPluginManager());
        $rpccm->addAbstractFactory($components);
        $rpccm->setServiceLocator($services);
    }

    public function testCreateRpcClient()
    {
        $rpcClient = $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
        $this->assertInstanceOf('HumusAmqpModule\RpcClient', $rpcClient);
    }
}
