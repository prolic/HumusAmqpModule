<?php

namespace HumusAmqpModuleTest\Service;

use HumusAmqpModule\PluginManager\Callback as CallbackPluginManager;
use HumusAmqpModule\PluginManager\Connection as ConnectionPluginManager;
use HumusAmqpModule\PluginManager\RpcServer as RpcServerPluginManager;
use HumusAmqpModule\Service\ConnectionAbstractServiceFactory;
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
        $config = array(
            'humus_amqp_module' => array(
                'classes' => array(
                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
                    'rpc_server' => 'HumusAmqpModule\Amqp\RpcServer',
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
                'rpc_servers' => array(
                    'test-rpc-server' => array(
                        'callback' => 'test-callback',
                        'qos_options' => array(
                            'prefetchSize' => 0,
                            'prefetchCount' => 0,
                            'global' => false
                        ),
                    ),
                ),
            )
        );

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $dependentComponent = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $cm = new ConnectionPluginManager());
        $cm->addAbstractFactory($dependentComponent);
        $cm->setServiceLocator($services);

        $components = $this->components = new RpcServerAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\RpcClient', $rpcsm = new RpcServerPluginManager());
        $rpcsm->addAbstractFactory($components);
        $rpcsm->setServiceLocator($services);

        $services->setService('HumusAmqpModule\PluginManager\Callback', $callbackManager = new CallbackPluginManager());
        $callbackManager->setInvokableClass('test-callback', __NAMESPACE__ . '\TestAsset\TestCallback');
        $callbackManager->setServiceLocator($services);
    }

    public function testCreateRpcServer()
    {
        $rpcServer = $this->components->createServiceWithName($this->services, 'test-rpc-server', 'test-rpc-server');
        $this->assertInstanceOf('HumusAmqpModule\Amqp\RpcServer', $rpcServer);
        /* @var $rpcServer \HumusAmqpModule\Amqp\RpcServer */
        $this->assertEquals('direct', $rpcServer->getExchangeOptions()->getType());
    }

    public function testCreateRpcServerWithCustomClass()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['rpc_servers']['test-rpc-server']['class'] = __NAMESPACE__
            . '\TestAsset\CustomRpcServer';
        $this->services->setService('Config', $config);

        $rpcServer = $this->components->createServiceWithName($this->services, 'test-rpc-server', 'test-rpc-server');
        $this->assertInstanceOf(__NAMESPACE__ . '\TestAsset\CustomRpcServer', $rpcServer);
        /* @var $rpcServer \HumusAmqpModule\Amqp\RpcServer */
        $this->assertEquals('direct', $rpcServer->getExchangeOptions()->getType());
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     * @expectedExceptionMessage Consumer of type stdClass is invalid; must extends HumusAmqpModule\Amqp\RpcServer
     */
    public function testCreateRpcServerWithInvalidCustomClass()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['rpc_servers']['test-rpc-server']['class'] = 'stdClass';
        $this->services->setService('Config', $config);

        $this->components->createServiceWithName($this->services, 'test-rpc-server', 'test-rpc-server');
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     * @expectedExceptionMessage callback is missing for rpc server
     */
    public function testCreateRpcServerWithoutCallback()
    {
        $config = $this->services->get('Config');
        unset($config['humus_amqp_module']['rpc_servers']['test-rpc-server']['callback']);
        $this->services->setService('Config', $config);

        $this->components->createServiceWithName($this->services, 'test-rpc-server', 'test-rpc-server');
    }
}
