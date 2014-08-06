<?php

namespace HumusAmqpModuleTest\Service;

use HumusAmqpModule\PluginManager\Callback as CallbackPluginManager;
use HumusAmqpModule\PluginManager\Connection as ConnectionPluginManager;
use HumusAmqpModule\PluginManager\MultipleConsumer as MultipleConsumerPluginManager;
use HumusAmqpModule\Service\ConnectionAbstractServiceFactory;
use HumusAmqpModule\Service\ConsumerAbstractServiceFactory;
use HumusAmqpModule\Service\MultipleConsumerAbstractServiceFactory;
use HumusAmqpModule\Service\ProducerAbstractServiceFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

class MultipleConsumerAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var ProducerAbstractServiceFactory
     */
    protected $components;

    public function setUp()
    {
        $config = array(
            'humus_amqp_module' => array(
                'classes' => array(
                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
                    'multiple_consumer' => 'HumusAmqpModule\Amqp\MultipleConsumer',
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
                'multiple_consumers' => array(
                    'test-consumer' => array(
                        'connection' => 'default',
                        /* 'class' => 'MyCustomConsumerClass' */
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct',
                        ),
                        'queues' => array(
                            array(
                                'name' => 'myconsumer-queue-1',
                                'callback' => __NAMESPACE__ . '\TestAsset\TestCallback',
                            ),
                            array(
                                'name' => 'myconsumer-queue-2',
                                'callback' => __NAMESPACE__ . '\TestAsset\TestCallback',
                            )
                        ),
                        'qos_options' => array(
                            'prefetchSize' => 0,
                            'prefetchCount' => 0
                        ),
                        'idle_timeout' => 20,
                        'auto_setup_fabric' => false,
                        'callback' => 'test-callback'
                    ),
                ),
            )
        );

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $dependentComponent = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $connectionManager = new ConnectionPluginManager());
        $connectionManager->addAbstractFactory($dependentComponent);
        $connectionManager->setServiceLocator($services);

        $callbackManager = new CallbackPluginManager();
        $callbackManager->setInvokableClass('test-callback', __NAMESPACE__ . '\TestAsset\TestCallback');
        $services->setService('HumusAmqpModule\PluginManager\Callback', $callbackManager);


        $callbackManager->setServiceLocator($services);

        $components = $this->components = new MultipleConsumerAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\MultipleConsumer', $consumerManager = new MultipleConsumerPluginManager());
        $consumerManager->addAbstractFactory($components);
        $consumerManager->setServiceLocator($services);
    }

    public function testCreateValidConsumer()
    {
        $consumer = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $consumer2 = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $this->assertNotSame($consumer, $consumer2);
        $this->assertInstanceOf('HumusAmqpModule\Amqp\MultipleConsumer', $consumer);
        /* @var $producer \HumusAmqpModule\Amqp\Producer */
        $this->assertEquals('demo-exchange', $consumer->getExchangeOptions()->getName());
        $this->assertEquals('direct', $consumer->getExchangeOptions()->getType());
        $queues = $consumer->getQueues();
        $this->assertCount(2, $queues);
        $this->assertTrue(array_key_exists('myconsumer-queue-1', $queues));
        $this->assertTrue(array_key_exists('myconsumer-queue-2', $queues));
        $queue = $queues['myconsumer-queue-1'];
        $this->assertEquals('myconsumer-queue-1', $queue->getName());
    }

    public function testCreateConsumerWithCustomClassAndWithoutConnectionName()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['multiple_consumers']['test-consumer']['class'] = __NAMESPACE__ . '\TestAsset\CustomMultipleConsumer';
        unset($config['humus_amqp_module']['consumers']['test-consumer']['connection']);
        $this->services->setService('Config', $config);

        $consumer = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $this->assertInstanceOf('HumusAmqpModuleTest\Service\TestAsset\CustomMultipleConsumer', $consumer);
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     * @expectedExceptionMessage Consumer of type stdClass is invalid; must implement HumusAmqpModule\Amqp\MultipleConsumerInterface
     */
    public function testCreateConsumerWithInvalidConsumerClass()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['multiple_consumers']['test-consumer']['class'] = 'stdClass';
        $this->services->setService('Config', $config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     * @expectedExceptionMessage Plugin of type stdClass is invalid; must be a callable
     */
    public function testCreateConsumerWithInvalidCallback()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['multiple_consumers']['test-consumer']['queues'][0]['callback'] = 'stdClass';
        $this->services->setService('Config', $config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }
}
