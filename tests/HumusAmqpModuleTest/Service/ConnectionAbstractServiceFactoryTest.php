<?php

namespace HumusAmqpModuleTest\Service;

use HumusAmqpModule\PluginManager\Connection as ConnectionPluginManager;
use HumusAmqpModule\Service\ConnectionAbstractServiceFactory;
use HumusAmqpModule\Service\ProducerAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

class ConnectionAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var ConnectionAbstractServiceFactory
     */
    protected $components;

    public function setUp()
    {
        $config = array(
            'humus_amqp_module' => array(
                'classes' => array(
                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
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
                )
            )
        );

        $services = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $components = $this->components = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $connectionManager = new ConnectionPluginManager());
        $connectionManager->addAbstractFactory($components);
        $connectionManager->setServiceLocator($services);
    }

    public function testMissingGlobalConfigIndicatesCannotCreateInstance()
    {
        $services = $this->services = new ServiceManager();
        $services->setAllowOverride(true);

        $components = $this->components = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $connectionManager = new ConnectionPluginManager());
        $connectionManager->addAbstractFactory($components);
        $connectionManager->setServiceLocator($services);

        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     * @expectedExceptionMessage Class "foobar" not found
     */
    public function testNotExistingConsumerClassResultsCannotCreateInstance()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['connections']['default']['class'] = 'foobar';
        $this->services->setService('Config', $config);

        $this->components->createServiceWithName($this->services, 'default', 'default');
    }

    public function testInvalidConsumerClassResultsCannotCreateInstance()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['connections']['default']['class'] = 'stdClass';
        $this->services->setService('Config', $config);

        $pm = $this->services->get('HumusAmqpModule\PluginManager\Connection');

        try {
            $pm->get('default');
        } catch (\Zend\ServiceManager\Exception\ServiceNotCreatedException $e) {
            // two exceptions backwards
            $p = $e->getPrevious()->getPrevious();
            $this->assertInstanceOf('HumusAmqpModule\Exception\RuntimeException', $p);
            $this->assertEquals(
                'Producer of type stdClass is invalid; must implement PhpAmqpLib\Connection\AbstractConnection',
                $p->getMessage()
            );
        }
    }

    public function testMissingConfigServiceIndicatesCannotCreateInstance()
    {
        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
        // second call give more code coverage (test lazy loading)
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

    public function testEmptyConnectionConfigIndicatesCannotCreateConnection()
    {
        $this->services->setService('Config', array('humus_amqp_module' => array()));
        $this->assertFalse(
            $this->components->canCreateServiceWithName($this->services, 'test-connection', 'test-connection')
        );
    }

    public function testMissingSpecIndicatesCannotCreateConnection()
    {
        $this->services->setService('Config', array(
            'humus_amqp_module' => array(
                'connections' => array(
                    'test-connection' => array()
                ),
            ),
        ));
        $this->assertFalse(
            $this->components->canCreateServiceWithName(
                $this->services,
                'test-connection',
                'test-connection'
            )
        );
    }

    public function testInvalidConnectionConfigIndicatesCannotCreateConnection()
    {
        $this->services->setService('Config', array(
            'humus_amqp_module' => array(
                'connections' => array(
                    'test-connection' => 'foobar'
                ),
            ),
        ));
        $this->assertFalse(
            $this->components->canCreateServiceWithName(
                $this->services,
                'test-connection',
                'test-connection'
            )
        );
    }

    public function testCorrectConfigIndicatesCanCreateConnection()
    {
        $this->services->setService('Config', array(
            'humus_amqp_module' => array(
                'connections' => array(
                    'test-connection' => array(
                        'lazy' => true
                    )
                ),
            ),
        ));
        $this->assertTrue(
            $this->components->canCreateServiceWithName(
                $this->services,
                'test-connection',
                'test-connection'
            )
        );
    }

    public function testLazyConnectionFactory()
    {
        $connection = $this->components->createServiceWithName(
            $this->services,
            'default',
            'default'
        );
        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPLazyConnection', $connection);

        $this->assertTrue(
            $this->components->canCreateServiceWithName(
                $this->services,
                'default',
                'default'
            )
        );

        $connection2 = $this->components->createServiceWithName(
            $this->services,
            'default',
            'default'
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
            'default',
            'default'
        );
        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPLazyConnection', $connection);
    }

    public function testNonLazyConnectionFactory()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['connections']['default']['lazy'] = false;

        $this->services->setService('Config', $config);

        try {
            $this->components->createServiceWithName(
                $this->services,
                'default',
                'default'
            );
        } catch (\PhpAmqpLib\Exception\AMQPRuntimeException $e) {
            // ignore exception
        }
    }
}
