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
use HumusAmqpModule\PluginManager\Consumer as ConsumerPluginManager;
use HumusAmqpModuleTest\Service\TestAsset\ConsumerAbstractServiceFactory;
use HumusAmqpModule\Service\ProducerAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

class ConsumerAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var ProducerAbstractServiceFactory
     */
    protected $components;

    public function prepare($config)
    {
        $connection = $this->getMockBuilder('AMQPConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $channel    = $this->getMockBuilder('AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();
        $channel
            ->expects($this->any())
            ->method('getPrefetchCount')
            ->will($this->returnValue(10));
        $queue      = $this->getMockBuilder('AMQPQueue')
            ->disableOriginalConstructor()
            ->getMock();
        $queue
            ->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));
        $queueFactory = $this->getMockBuilder('HumusAmqpModule\QueueFactory')->getMock();
        $queueFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($queue));

        $connectionManager = $this->getMockBuilder('HumusAmqpModule\PluginManager\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionManager
            ->expects($this->any())
            ->method('get')
            ->with('default')
            ->willReturn($connection);

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('config', $config);

        $callbackManager = new CallbackPluginManager($services);
        $callbackManager->setInvokableClass('test-callback', __NAMESPACE__ . '\TestAsset\TestCallback');

        $services->setService('HumusAmqpModule\PluginManager\Connection', $connectionManager);
        $services->setService('HumusAmqpModule\PluginManager\Callback', $callbackManager);

        $components = $this->components = new ConsumerAbstractServiceFactory();
        $components->setChannelMock($channel);
        $components->setQueueFactory($queueFactory);

        $consumerManager = new ConsumerPluginManager($services);

        $services->setService('HumusAmqpModule\PluginManager\Consumer', $consumerManager);
        $consumerManager->addAbstractFactory($components);
    }

    public function testCreateConsumer()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'callback' => 'test-callback',
                        'flush_callback' => 'test-callback',
                        'error_callback' => 'test-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $consumer = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $consumer2 = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $this->assertNotSame($consumer, $consumer2);
        $this->assertInstanceOf('HumusAmqpModule\Consumer', $consumer);
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage The logger invalid stuff is not configured
     */
    public function testCreateConsumerThrowsExceptionOnInvalidLogger()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'logger' => 'invalid stuff',
                        'callback' => 'test-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage The required callback invalid-callback can not be found
     */
    public function testCreateConsumerThrowsExceptionOnInvalidCallback()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'callback' => 'invalid-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage The required callback invalid-callback can not be found
     */
    public function testCreateConsumerThrowsExceptionOnInvalidFlushCallback()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'callback' => 'test-callback',
                        'flush_callback' => 'invalid-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage The required callback invalid-callback can not be found
     */
    public function testCreateConsumerThrowsExceptionOnInvalidErrorCallback()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'callback' => 'test-callback',
                        'error_callback' => 'invalid-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage No delivery callback specified for consumer test-consumer
     */
    public function testCreateConsumerThrowsExceptionOnMissingCallback()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'error_callback' => 'invalid-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage Queues are missing for consumer test-consumer
     */
    public function testCreateConsumerThrowsExceptionOnMissingQueues()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'auto_setup_fabric' => false,
                        'callback' => 'test-callback',
                        'error_callback' => 'invalid-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage Queue invalid-queue is missing in the queue configuration
     */
    public function testCreateConsumerThrowsExceptionOnMissingQueue()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['invalid-queue'],
                        'auto_setup_fabric' => false,
                        'callback' => 'test-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage The queues exchange demo-exchange is missing in the exchanges configuration
     */
    public function testCreateConsumerThrowsExceptionOnQueuesExchangeMissing()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'callback' => 'test-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    public function testCreateConsumerThrowsExceptionOnQueueConnectionMismatch()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange',
                        'connection' => 'other'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'callback' => 'test-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->setExpectedException(
            'HumusAmqpModule\Exception\InvalidArgumentException',
            'The queue connection for queue demo-queue (other) does not match the consumer '
            . 'connection for consumer test-consumer (default)'
        );

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    public function testCreateConsumerThrowsExceptionOnQueueToExchangeConnectionMismatch()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct',
                        'connection' => 'other'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange',
                        'connection' => 'default'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'callback' => 'test-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->setExpectedException(
            'HumusAmqpModule\Exception\InvalidArgumentException',
            'The exchange connection for exchange demo-exchange (other) does not match the '
            . 'consumer connection for consumer test-consumer (default)'
        );

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    public function testCreateConsumerWithCustomLogger()
    {
        $config = [
            'log' => [
                'consumer-logger' => [
                    'writers' => [
                        [
                            'name' => 'stream',
                            'priority' => 1000,
                            'options' => [
                                'stream' => sys_get_temp_dir() . '/consumers.log'
                            ]
                        ]
                    ]
                ]
            ],
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'logger' => 'consumer-logger',
                        'callback' => 'test-callback',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ],
        ];

        $this->prepare($config);
        $this->services->addAbstractFactory(new \Zend\Log\LoggerAbstractServiceFactory());

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     * @expectedExceptionMessage The logger foo is not a Psr\Log
     */
    public function testCreateConsumerWithInvalidLogger()
    {
        $config = [
            'humus_amqp_module' => [
                'default_connection' => 'default',
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct'
                    ]
                ],
                'queues' => [
                    'demo-queue' => [
                        'name' => 'demo-queue',
                        'exchange' => 'demo-exchange'
                    ]
                ],
                'consumers' => [
                    'test-consumer' => [
                        'connection' => 'default',
                        'queues' => ['demo-queue'],
                        'auto_setup_fabric' => false,
                        'callback' => 'invalid-callback',
                        'logger' => 'foo',
                        'qos' => [
                            'prefetchCount' => 10
                        ]
                    ],
                ],
            ]
        ];

        $this->prepare($config);

        $this->services->setService('foo', new \stdClass());

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }
}
