<?php

namespace HumusAmqpModuleTest\Service;

use HumusAmqpModule\PluginManager\Connection as ConnectionPluginManager;
use HumusAmqpModule\PluginManager\Producer as ProducerPluginManager;
use HumusAmqpModule\Service\ConnectionAbstractServiceFactory;
use HumusAmqpModule\Service\ProducerAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

class ProducerAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
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
                    'producer' => 'HumusAmqpModule\Amqp\Producer',
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
                        'class' => __NAMESPACE__ . '\\TestAsset\\CustomProducer',
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct'
                        ),
                        'queue_options' => array(
                            'passive' => false
                        ),
                        'auto_setup_fabric' => false
                    ),
                    'test-producer-2' => array(
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct'
                        )
                    ),
                )
            )
        );

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $dependentComponent = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $connectionManager = new ConnectionPluginManager());
        $connectionManager->addAbstractFactory($dependentComponent);
        $connectionManager->setServiceLocator($services);

        $components = $this->components = new ProducerAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Producer', $producerManager = new ProducerPluginManager());
        $producerManager->addAbstractFactory($components);
        $producerManager->setServiceLocator($services);
    }

    public function testCreateValidProducer()
    {
        $producer = $this->components->createServiceWithName($this->services, 'test-producer', 'test-producer');
        $this->assertInstanceOf('HumusAmqpModule\Amqp\Producer', $producer);
        /* @var $producer \HumusAmqpModule\Amqp\Producer */
        $this->assertEquals('demo-exchange', $producer->getExchangeOptions()->getName());
        $this->assertEquals('direct', $producer->getExchangeOptions()->getType());
    }

    public function testCreateValidProducerWithoutConnectionName()
    {
        $producer = $this->components->createServiceWithName($this->services, 'test-producer-2', 'test-producer-2');
        $this->assertInstanceOf('HumusAmqpModule\Amqp\Producer', $producer);
        /* @var $producer \HumusAmqpModule\Amqp\Producer */
        $this->assertEquals('demo-exchange', $producer->getExchangeOptions()->getName());
        $this->assertEquals('direct', $producer->getExchangeOptions()->getType());
    }

    public function testInvalidConsumerClassResultsCannotCreateInstance()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['producers']['test-producer']['class'] = 'stdClass';
        $this->services->setService('Config', $config);

        $pm = $this->services->get('HumusAmqpModule\PluginManager\Producer');
        try {
            $pm->get('test-producer');
        } catch (\Zend\ServiceManager\Exception\ServiceNotCreatedException $e) {
            // two exceptions backwards
            $p = $e->getPrevious()->getPrevious();
            $this->assertInstanceOf('HumusAmqpModule\Exception\RuntimeException', $p);
            $this->assertEquals(
                'Producer of type stdClass is invalid; must implement HumusAmqpModule\Amqp\Producer',
                $p->getMessage()
            );
        }
    }

    public function testCannotCreateProducerWhenConnectionPluginManagerIsMissing()
    {
        $config = array(
            'humus_amqp_module' => array(
                'classes' => array(
                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
                    'producer' => 'HumusAmqpModule\Amqp\Producer',
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
                        'class' => __NAMESPACE__ . '\\TestAsset\\CustomProducer',
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct'
                        )
                    ),
                    'test-producer-2' => array(
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct'
                        )
                    ),
                )
            )
        );

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $components = $this->components = new ProducerAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Producer', $producerManager = new ProducerPluginManager());
        $producerManager->addAbstractFactory($components);
        $producerManager->setServiceLocator($services);

        try {
            $producerManager->get('test-producer');
        } catch (\Zend\ServiceManager\Exception\ServiceNotCreatedException $e) {
            $p = $e->getPrevious()->getPrevious();
            $this->assertInstanceOf('HumusAmqpModule\Exception\RuntimeException', $p);
            $this->assertEquals('HumusAmqpModule\PluginManager\Connection not found', $p->getMessage());
        }
    }
}
