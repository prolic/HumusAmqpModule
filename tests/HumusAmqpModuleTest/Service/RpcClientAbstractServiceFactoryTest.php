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

    protected function prepare($config)
    {
        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

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

        $connectionManager = $this->getMock('HumusAmqpModule\PluginManager\Connection');
        $connectionManager
            ->expects($this->any())
            ->method('get')
            ->with('default')
            ->willReturn($connection);

        $dependentComponent = new ConnectionAbstractServiceFactory();
        $this->services->setService('HumusAmqpModule\PluginManager\Connection', $cm = new ConnectionPluginManager());
        $cm->addAbstractFactory($dependentComponent);
        $cm->setServiceLocator($this->services);

        $components = $this->components = new TestAsset\RpcClientAbstractServiceFactory();
        $components->setChannelMock($channel);
        $components->setQueueFactory($queueFactory);
        $this->services->setService('HumusAmqpModule\PluginManager\RpcClient', $rpccm = new RpcClientPluginManager());
        $rpccm->addAbstractFactory($components);
        $rpccm->setServiceLocator($this->services);
    }

    public function testCreateRpcClient()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'connections' => [
                    'default' => [
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                    ]
                ],
                'exchanges' => [
                    'test-rpc-client' => [
                        'name' => 'test-rpc-client',
                        'type' => 'direct'
                    ],
                ],
                'queues' => [
                    'test-rpc-client' => [
                        'name' => '',
                        'exchange' => 'test-rpc-client'
                    ],
                ],
                'rpc_clients' => [
                    'test-rpc-client' => [
                        'queue' => 'test-rpc-client'
                    ]
                ]
            ]
        ];

        $this->prepare($config);

        $rpcClient = $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
        $this->assertInstanceOf('HumusAmqpModule\RpcClient', $rpcClient);
    }

    public function testCreateRpcClientWithDefinedConnection()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'connections' => [
                    'default' => [
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                    ]
                ],
                'exchanges' => [
                    'test-rpc-client' => [
                        'name' => 'test-rpc-client',
                        'type' => 'direct'
                    ],
                ],
                'queues' => [
                    'test-rpc-client' => [
                        'name' => '',
                        'exchange' => 'test-rpc-client'
                    ],
                ],
                'rpc_clients' => [
                    'test-rpc-client' => [
                        'queue' => 'test-rpc-client',
                        'connection' => 'default'
                    ]
                ]
            ]
        ];

        $this->prepare($config);

        $rpcClient = $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
        $this->assertInstanceOf('HumusAmqpModule\RpcClient', $rpcClient);
    }

    /**
     * @expectedException HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage The rpc client queue false-rpc-client-queue-name is missing in the queues configuration
     */
    public function testCreateRpcClientThrowsExceptionOnInvalidQueueName()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'connections' => [
                    'default' => [
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                    ]
                ],
                'exchanges' => [
                    'test-rpc-client' => [
                        'name' => 'test-rpc-client',
                        'type' => 'direct'
                    ],
                ],
                'queues' => [
                    'test-rpc-client' => [
                        'name' => '',
                        'exchange' => 'test-rpc-client'
                    ],
                ],
                'rpc_clients' => [
                    'test-rpc-client' => [
                        'queue' => 'false-rpc-client-queue-name'
                    ]
                ]
            ]
        ];

        $this->prepare($config);

        $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
    }

    /**
     * @expectedException HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage Queue is missing for rpc client test-rpc-client
     */
    public function testCreateRpcClientThrowsExceptionOnMissingQueue()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'rpc_clients' => [
                    'test-rpc-client' => [
                    ]
                ]
            ]
        ];

        $this->prepare($config);

        $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
    }

    public function testCreateRpcClientThrowsExceptionOnConnectionMismatch()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'connections' => [
                    'default' => [
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                    ]
                ],
                'exchanges' => [
                    'test-rpc-client' => [
                        'name' => 'test-rpc-client',
                        'type' => 'direct'
                    ],
                ],
                'queues' => [
                    'test-rpc-client' => [
                        'name' => '',
                        'exchange' => 'test-rpc-client',
                        'connection' => 'someother'
                    ],
                ],
                'rpc_clients' => [
                    'test-rpc-client' => [
                        'queue' => 'test-rpc-client',
                        'connection' => 'default'
                    ]
                ]
            ]
        ];

        $this->prepare($config);

        $this->setExpectedException(
            'HumusAmqpModule\Exception\InvalidArgumentException',
            'The rpc client connection for queue test-rpc-client (someother) does not match the rpc client '
            . 'connection for rpc client test-rpc-client (default)'
        );

        $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
    }
}
