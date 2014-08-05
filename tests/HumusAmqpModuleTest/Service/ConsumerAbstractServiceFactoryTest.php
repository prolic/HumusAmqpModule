<?php

namespace HumusAmqpModuleTest\Service;

use HumusAmqpModule\PluginManager\Callback as CallbackPluginManager;
use HumusAmqpModule\PluginManager\Connection as ConnectionPluginManager;
use HumusAmqpModule\PluginManager\Consumer as ConsumerPluginManager;
use HumusAmqpModule\Service\ConnectionAbstractServiceFactory;
use HumusAmqpModule\Service\ConsumerAbstractServiceFactory;
use HumusAmqpModule\Service\ProducerAbstractServiceFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
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

    public function setUp()
    {
        $config = array(
            'humus_amqp_module' => array(
                'classes' => array(
                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
                    'consumer' => 'HumusAmqpModule\Amqp\Consumer',
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


        $serviceConfig = new ServiceManagerConfig(array(
            'invokables' => array(
                'test-callback' => __NAMESPACE__ . '\TestAsset\TestCallback'
            )
        ));
        $services->setService('HumusAmqpModule\PluginManager\Callback', $callbackManager = new CallbackPluginManager($serviceConfig));
        $callbackManager->setServiceLocator($services);

        $components = $this->components = new ConsumerAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Consumer', $consumerManager = new ConsumerPluginManager());
        $consumerManager->addAbstractFactory($components);
        $consumerManager->setServiceLocator($services);
    }

    public function testCreateValidConsumer()
    {
        $consumer = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $consumer2 = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $this->assertNotSame($consumer, $consumer2);
        $this->assertInstanceOf('HumusAmqpModule\Amqp\Consumer', $consumer);
        /* @var $producer \HumusAmqpModule\Amqp\Producer */
        $this->assertEquals('demo-exchange', $consumer->getExchangeOptions()->getName());
        $this->assertEquals('direct', $consumer->getExchangeOptions()->getType());
        $this->assertEquals('myconsumer-queue', $consumer->getQueueOptions()->getName());
    }

    public function testCreateConsumerWithCustomClassAndWithoutConnectionName()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['consumers']['test-consumer']['class'] = __NAMESPACE__ . '\TestAsset\CustomConsumer';
        unset($config['humus_amqp_module']['consumers']['test-consumer']['connection']);
        $this->services->setService('Config', $config);

        $consumer = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $this->assertInstanceOf('HumusAmqpModuleTest\Service\TestAsset\CustomConsumer', $consumer);
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     * @expectedExceptionMessage Consumer of type stdClass is invalid; must implement HumusAmqpModule\Amqp\ConsumerInterface
     */
    public function testCreateConsumerWithInvalidConsumerClass()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['consumers']['test-consumer']['class'] = 'stdClass';
        $this->services->setService('Config', $config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }
}
