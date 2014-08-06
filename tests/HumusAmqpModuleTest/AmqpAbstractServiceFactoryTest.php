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

namespace HumusPHPUnitModuleTest;

use HumusAmqpModule\AmqpAbstractServiceFactory;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;

//class AmqpAbstractServiceFactoryTest extends TestCase
//{
//    /**
//     * @var ServiceManager
//     */
//    protected $services;
//
//    /**
//     * @var AmqpAbstractServiceFactory
//     */
//    protected $components;
//
//    public function setUp()
//    {
//        $config = array(
//            'humus_amqp_module' => array(
//                'classes' => array(
//                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
//                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
//                    'producer' => 'HumusAmqpModule\Amqp\Producer',
//                    'consumer' => 'HumusAmqpModule\Amqp\Consumer',
//                    'multiple_consumer' => 'HumusAmqpModule\Amqp\MultipleConsumer',
//                    'anon_consumer' => 'HumusAmqpModule\Amqp\AnonConsumer',
//                    'rpc_client' => 'HumusAmqpModule\Amqp\RpcClient',
//                    'rpc_server' => 'HumusAmqpModule\Amqp\RpcServer',
//                    'logged_channel' => 'HumusAmqpModule\Amqp\AMQPLoggedChannel',
//                    'parts_holder' => 'HumusAmqpModule\Amqp\PartsHolder',
//                    'fallback' => 'HumusAmqpModule\Amqp\Fallback'
//                ),
//                'connections' => array(
//                    'default' => array(
//                        'host' => 'localhost',
//                        'port' => 5672,
//                        'user' => 'guest',
//                        'password' => 'guest',
//                        'vhost' => '/',
//                        'lazy' => true
//                    )
//                ),
//                'producers' => array(
//                    'test-producer' => array(
//                        'connection' => 'default',
//                        /* 'class' => 'MyCustomProducerClass' */
//                        'exchange_options' => array(
//                            'name' => 'demo-exchange',
//                            'type' => 'direct'
//                        )
//                    ),
//                ),
//                'consumers' => array(
//                    'test-consumer' => array(
//                        'connection' => 'default',
//                        /* 'class' => 'MyCustomConsumerClass' */
//                        'exchange_options' => array(
//                            'name' => 'demo-exchange',
//                            'type' => 'direct',
//                        ),
//                        'queue_options' => array(
//                            'name' => 'myconsumer-queue',
//                        ),
//                        'auto_setup_fabric' => true,
//                        'callback' => 'test-callback'
//                    ),
//                ),
//                'anon_consumers' => array(
//                    'test-anon-consumer' => array(
//                        'connection' => 'default',
//                        /* 'class' => 'MyCustomConsumerClass' */
//                        'exchange_options' => array(
//                            'name' => 'demo-exchange',
//                            'type' => 'direct',
//                        ),
//                        'queue_options' => array(
//                            'name' => 'myconsumer-queue',
//                        ),
//                        'auto_setup_fabric' => true,
//                        'callback' => 'test-callback'
//                    ),
//                ),
//                'multiple_consumers' => array(
//                    'test-mconsumer' => array(
//                        'connection' => 'default',
//                        /* 'class' => 'MyCustomConsumerClass' */
//                        'exchange_options' => array(
//                            'name' => 'demo-exchange',
//                            'type' => 'direct',
//                        ),
//                        'queues' => array(
//                            array(
//                                'name' => 'multi-1',
//                                'callback' => 'test-callback'
//                            ),
//                        ),
//                        'auto_setup_fabric' => true,
//                    ),
//                ),
//                'rpc_servers' => array(
//                    'test-rpc-server' => array(
//                        'connection' => 'default',
//                        'callback' => 'test-callback'
//                    ),
//                ),
//                'rpc_clients' => array(
//                    'test-rpc-client' => array(
//                        'connection' => 'default',
//                        'expect_serialized_response' => true
//                    )
//                )
//            )
//        );
//
//        $callback = function ($msg) {
//            echo $msg->body . "\n";
//        };
//
//        $services    = $this->services = new ServiceManager();
//        $services->setAllowOverride(true);
//
//        $services->setService('Config', $config);
//        $services->setService('test-callback', $callback);
//
//        $compontents = $this->components = new AmqpAbstractServiceFactory();
//        $services->addAbstractFactory($compontents);
//    }
//
//    public function testMissingConfigServiceIndicatesCannotCreateInstance()
//    {
//        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
//    }
//
//    public function testMissinAmqpServicePrefixIndicatesCannotCreateInstance()
//    {
//        $this->services->setService('Config', array());
//        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
//    }
//
//    public function testInvalidConfigIndicatesCannotCreateInstance()
//    {
//        $this->services->setService('Config', array('humus_amqp_module' => 'string'));
//        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
//    }
//
//    public function testEmptyConsumerConfigIndicatesCannotCreateConsumer()
//    {
//        $this->services->setService('Config', array('humus_amqp_module' => array()));
//        $this->assertFalse(
//            $this->components->canCreateServiceWithName($this->services, 'test-consumer', 'test-consumer')
//        );
//    }
//
//    public function testLazyConnectionFactory()
//    {
//        $connection = $this->components->createServiceWithName(
//            $this->services,
//            'HumusAmqpModule\default',
//            'HumusAmqpModule\default'
//        );
//        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPLazyConnection', $connection);
//
//        $this->assertTrue(
//            $this->components->canCreateServiceWithName(
//                $this->services,
//                'HumusAmqpModule\default',
//                'HumusAmqpModule\default'
//            )
//        );
//
//        $connection2 = $this->components->createServiceWithName(
//            $this->services,
//            'HumusAmqpModule\default',
//            'HumusAmqpModule\default'
//        );
//
//        $this->assertSame($connection, $connection2);
//    }
//
//    public function testLazyConnectionWithMissingConfigFactory()
//    {
//        $config = $this->services->get('Config');
//        unset($config['humus_amqp_module']['connections']['default']['lazy']);
//
//        $this->services->setService('Config', $config);
//
//        $connection = $this->components->createServiceWithName(
//            $this->services,
//            'HumusAmqpModule\default',
//            'HumusAmqpModule\default'
//        );
//        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPLazyConnection', $connection);
//    }
//
//    public function testNonLazyConnectionFactory()
//    {
//        $config = $this->services->get('Config');
//        $config['humus_amqp_module']['connections']['default']['lazy'] = false;
//
//        $this->services->setService('Config', $config);
//
//        try {
//            $this->components->createServiceWithName(
//                $this->services,
//                'HumusAmqpModule\default',
//                'HumusAmqpModule\default'
//            );
//        } catch (\PhpAmqpLib\Exception\AMQPRuntimeException $e) {
//            // ignore exception
//        }
//    }
//
//    public function testMissingSpecIndicatesCannotCreateConsumer()
//    {
//        $this->services->setService('Config', array(
//            'humus_amqp_module' => array(
//                'consumers' => array(
//                    'test-consumer' => array()
//                ),
//            ),
//        ));
//        $this->assertFalse(
//            $this->components->canCreateServiceWithName(
//                $this->services,
//                'test-consumer',
//                'test-consumer'
//            )
//        );
//    }
//
//    public function testInvalidConsumerConfigIndicatesCannotCreateConsumer()
//    {
//        $this->services->setService('Config', array(
//            'humus_amqp_module' => array(
//                'consumers' => array(
//                    'test-consumer' => 'foobar'
//                ),
//            ),
//        ));
//        $this->assertFalse(
//            $this->components->canCreateServiceWithName(
//                $this->services,
//                'test-consumer',
//                'test-consumer'
//            )
//        );
//    }
//
//    public function testValidConsumerCanBeCreated()
//    {
//        $this->assertTrue(
//            $this->components->canCreateServiceWithName(
//                $this->services,
//                'test-consumer',
//                'test-consumer'
//            )
//        );
//    }
//
//    public function testValidConsumerCreation()
//    {
//        $consumer = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
//        $this->assertInstanceOf('HumusAmqpModule\Amqp\Consumer', $consumer);
//
//        $consumer2 = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
//        $this->assertNotSame($consumer, $consumer2);
//    }
//
//    public function testValidProducerCreation()
//    {
//        $producer = $this->components->createServiceWithName($this->services, 'test-producer', 'test-producer');
//        $this->assertInstanceOf('HumusAmqpModule\Amqp\Producer', $producer);
//
//        $producer2 = $this->components->createServiceWithName($this->services, 'test-producer', 'test-producer');
//        $this->assertSame($producer, $producer2);
//    }
//
//    public function testValidMultipleConsumerCreation()
//    {
//        $mconsumer = $this->components->createServiceWithName($this->services, 'test-mconsumer', 'test-mconsumer');
//        $this->assertInstanceOf('HumusAmqpModule\Amqp\MultipleConsumer', $mconsumer);
//
//        $mconsumer2 = $this->components->createServiceWithName($this->services, 'test-mconsumer', 'test-mconsumer');
//        $this->assertNotSame($mconsumer, $mconsumer2);
//    }
//
//    public function testValidAnonConsumerCreation()
//    {
//        $aconsumer = $this->components->createServiceWithName($this->services, 'test-anon-consumer', 'test-anon-consumer');
//        $this->assertInstanceOf('HumusAmqpModule\Amqp\AnonConsumer', $aconsumer);
//
//        $aconsumer2 = $this->components->createServiceWithName($this->services, 'test-anon-consumer', 'test-anon-consumer');
//        $this->assertNotSame($aconsumer, $aconsumer2);
//    }
//
//    public function testValidRpcClientCreation()
//    {
//        $rpcClient = $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
//        $this->assertInstanceOf('HumusAmqpModule\Amqp\RpcClient', $rpcClient);
//
//        $rpcClient2 = $this->components->createServiceWithName($this->services, 'test-rpc-client', 'test-rpc-client');
//        $this->assertSame($rpcClient, $rpcClient2);
//    }
//
//    public function testValidRpcServerCreation()
//    {
//        $rpcServer = $this->components->createServiceWithName($this->services, 'test-rpc-server', 'test-rpc-server');
//        $this->assertInstanceOf('HumusAmqpModule\Amqp\RpcServer', $rpcServer);
//
//        $rpcServer2 = $this->components->createServiceWithName($this->services, 'test-rpc-server', 'test-rpc-server');
//        $this->assertNotSame($rpcServer, $rpcServer2);
//    }
//}
