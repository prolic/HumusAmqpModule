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

use HumusAmqpModule\PluginManager\Callback as CallbackPluginManager;
use HumusAmqpModule\PluginManager\RpcServer as RpcServerPluginManager;
use HumusAmqpModule\Service\RpcServerAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

class RpcServerAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var RpcServerAbstractServiceFactory
     */
    protected $components;

    public function setUp()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'test-rpc-server' => [
                        'name' => 'test-rpc-server',
                        'type' => 'direct'
                    ],
                ],
                'queues' => [
                    'test-rpc-server' => [
                        'name' => 'test-rpc-server',
                        'exchange' => 'test-rpc-server'
                    ],
                ],
                'rpc_servers' => [
                    'test-rpc-server' => [
                        'connection' => 'default',
                        'queue' => 'test-rpc-server',
                        'callback' => 'test-callback',
                        'qos' => [
                            'prefetchSize' => 0,
                            'prefetchCount' => 1,
                        ],
                    ],
                ],
            ]
        ];

        $connection = $this->getMock('AMQPConnection', [], [], '', false);
        $channel    = $this->getMock('AMQPChannel', [], [], '', false);
        $channel
            ->expects($this->any())
            ->method('getPrefetchCount')
            ->will($this->returnValue(10));
        $queue      = $this->getMock('AMQPQueue', [], [], '', false);
        $queue
            ->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));
        $queueFactory = $this->getMock('HumusAmqpModule\QueueFactory');
        $queueFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($queue));

        $connectionManager = $this->getMock('HumusAmqpModule\PluginManager\Connection', [], [], '', false);
        $connectionManager
            ->expects($this->any())
            ->method('get')
            ->with('default')
            ->willReturn($connection);

        $customLog = $this->getMock('Zend\Log\LoggerInterface');

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('config', $config);

        $callbackManager = new CallbackPluginManager($services);
        $callbackManager->setInvokableClass('test-callback', __NAMESPACE__ . '\TestAsset\TestCallback');

        $services->setService('HumusAmqpModule\PluginManager\Connection', $connectionManager);
        $services->setService('HumusAmqpModule\PluginManager\Callback', $callbackManager);
        $services->setService('custom-log', $customLog);

        $components = $this->components = new TestAsset\RpcServerAbstractServiceFactory();
        $components->setChannelMock($channel);
        $components->setQueueFactory($queueFactory);

        $rpcsm = new RpcServerPluginManager($services);

        $services->setService('HumusAmqpModule\PluginManager\RpcClient', $rpcsm);
        $rpcsm->addAbstractFactory($components);

        $callbackManager = new CallbackPluginManager($services);

        $services->setService('HumusAmqpModule\PluginManager\Callback', $callbackManager);
        $callbackManager->setInvokableClass('test-callback', __NAMESPACE__ . '\TestAsset\TestCallback');
    }

    public function testCreateRpcServer()
    {
        $rpcServer = $this->components->createServiceWithName($this->services, 'test-rpc-server', 'test-rpc-server');
        $this->assertInstanceOf('HumusAmqpModule\RpcServer', $rpcServer);
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage Callback is missing for rpc server test-rpc-server
     */
    public function testCreateRpcServerWithoutCallback()
    {
        $config = $this->services->get('config');
        unset($config['humus_amqp_module']['rpc_servers']['test-rpc-server']['callback']);
        $this->services->setService('config', $config);

        $this->components->createServiceWithName($this->services, 'test-rpc-server', 'test-rpc-server');
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage The logger invalid stuff is not configured
     */
    public function testCreateConsumerThrowsExceptionOnInvalidLogger()
    {
        $config = $this->services->get('config');
        $config['humus_amqp_module']['rpc_servers']['test-rpc-server']['logger'] = 'invalid stuff';
        $this->services->setService('config', $config);

        $this->components->createServiceWithName($this->services, 'test-rpc-server', 'test-rpc-server');
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage The logger foo is not a Psr\Log
     */
    public function testCreateConsumerWithInvalidLogger()
    {
        $config = $this->services->get('config');
        $config['humus_amqp_module']['rpc_servers']['test-rpc-server']['logger'] = 'foo';
        $this->services->setService('config', $config);
        $this->services->setService('foo', new \stdClass());

        $this->components->createServiceWithName($this->services, 'test-rpc-server', 'test-rpc-server');
    }
}
