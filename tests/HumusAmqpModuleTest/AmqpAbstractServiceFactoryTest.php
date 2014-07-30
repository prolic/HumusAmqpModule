<?php

namespace HumusPHPUnitModuleTest;

use HumusAmqpModule\AmqpAbstractServiceFactory;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;

class AmqpAbstractServiceFactoryTest extends TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var AmqpAbstractServiceFactory
     */
    protected $components;

    public function setUp()
    {
        $config = array(
            'humus_amqp_module' => array(
                'classes' => array(
                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
                    'producer' => 'HumusAmqpModule\Amqp\Producer',
                    'consumer' => 'HumusAmqpModule\Amqp\Consumer',
                    'multi_consumer' => 'HumusAmqpModule\Amqp\MultipleConsumer',
                    'anon_consumer' => 'HumusAmqpModule\Amqp\AnonConsumer',
                    'rpc_client' => 'HumusAmqpModule\Amqp\RpcClient',
                    'rpc_server' => 'HumusAmqpModule\Amqp\RpcServer',
                    'logged_channel' => 'HumusAmqpModule\Amqp\AMQPLoggedChannel',
                    'parts_holder' => 'HumusAmqpModule\Amqp\PartsHolder',
                    'fallback' => 'HumusAmqpModule\Amqp\Fallback'
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
                'producers' => array(
                    'test-producer' => array(
                        'connection' => 'default',
                        /* 'class' => 'MyCustomProducerClass' */
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct'
                        )
                    ),
                ),
                'consumers' => array(
                    'test-consumer' => array(
                        'connection' => 'default',
                        /* 'class' => 'MyCustomConsumerClass' */
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct',
                        ),
                        'queue_options' => array(
                            'name' => 'myconsumer-queue',
                        ),
                        'auto_setup_fabric' => true,
                        'callback' => 'test-callback'
                    ),
                )
            )
        );

        $callback = function ($msg) {
            echo $msg->body . "\n";
        };

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);

        $services->setService('Config', $config);
        $services->setService('test-callback', $callback);

        $compontents = $this->components = new AmqpAbstractServiceFactory();
        $services->addAbstractFactory($compontents);
    }

    public function testMissingConfigServiceIndicatesCannotCreateInstance()
    {
        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
    }

    public function testMissinAmqpServicePrefixIndicatesCannotCreateInstance()
    {
        $this->services->setService('Config', array());
        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
    }

    public function testInvalidConfigIndicatesCannotCreateInstance()
    {
        $this->services->setService('Config', array('humus_amqp_module' => 'string'));
        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
    }

    public function testEmptyConsumerConfigIndicatesCannotCreateConsumer()
    {
        $this->services->setService('Config', array('humus_amqp_module' => array()));
        $this->assertFalse(
            $this->components->canCreateServiceWithName($this->services, 'test-consumer', 'test-consumer')
        );
    }

    public function testLazyConnectionFactory()
    {
        $connection = $this->components->createServiceWithName(
            $this->services,
            'HumusAmqpModule\default',
            'HumusAmqpModule\default'
        );
        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPLazyConnection', $connection);

        $this->assertTrue(
            $this->components->canCreateServiceWithName(
                $this->services,
                'HumusAmqpModule\default',
                'HumusAmqpModule\default'
            )
        );

        $connection2 = $this->components->createServiceWithName(
            $this->services,
            'HumusAmqpModule\default',
            'HumusAmqpModule\default'
        );

        $this->assertSame($connection, $connection2);
    }

    public function testLazyConnectionWithMissingConfigFactory()
    {
        $config = $this->services->get('Config');
        unset($config['humus_amqp_module']['connections']['default']['lazy']);

        $this->services->setService('Config', $config);

        $connection = $this->components->createServiceWithName(
            $this->services,
            'HumusAmqpModule\default',
            'HumusAmqpModule\default'
        );
        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPLazyConnection', $connection);
    }

    /**
     * @expectedException PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function testNonLazyConnectionFactory()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['connections']['default']['lazy'] = false;

        $this->services->setService('Config', $config);
        $this->components->createServiceWithName(
            $this->services,
            'HumusAmqpModule\default',
            'HumusAmqpModule\default'
        );
    }

    public function testMissingSpecIndicatesCannotCreateConsumer()
    {
        $this->services->setService('Config', array(
            'humus_amqp_module' => array(
                'consumers' => array(
                    'test-consumer' => array()
                ),
            ),
        ));
        $this->assertFalse(
            $this->components->canCreateServiceWithName(
                $this->services,
                'test-consumer',
                'test-consumer'
            )
        );
    }

    public function testInvalidConsumerConfigIndicatesCannotCreateConsumer()
    {
        $this->services->setService('Config', array(
            'humus_amqp_module' => array(
                'consumers' => array(
                    'test-consumer' => 'foobar'
                ),
            ),
        ));
        $this->assertFalse(
            $this->components->canCreateServiceWithName(
                $this->services,
                'test-consumer',
                'test-consumer'
            )
        );
    }

    public function testValidConsumerCanBeCreated()
    {
        $this->assertTrue(
            $this->components->canCreateServiceWithName(
                $this->services,
                'test-consumer',
                'test-consumer'
            )
        );
    }

    public function testValidConsumerCreation()
    {
        $consumer = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $this->assertInstanceOf('HumusAmqpModule\Amqp\Consumer', $consumer);

        $consumer2 = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $this->assertNotSame($consumer, $consumer2);
    }
}
