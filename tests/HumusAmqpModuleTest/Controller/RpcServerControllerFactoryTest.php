<?php

namespace HumusAmqpModuleTest\Controller;

use HumusAmqpModule\Controller\RpcServerController as Controller;
use HumusAmqpModule\Controller\RpcServerControllerFactory as ControllerFactory;
use HumusAmqpModule\PluginManager\RpcServer;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceManager;

class RpcServerControllerFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ControllerFactory
     */
    protected $controllerFactory;
    /**
     * @var ServiceManager
     */
    protected $serviceLocator;
    /**
     * @var RpcServer
     */
    protected $manager;

    public function setUp()
    {
        $manager = $this->prophesize(RpcServer::class);
        $serviceLocator = $this->prophesize(ServiceManager::class);
        $serviceLocator->get(RpcServer::class)->willReturn($manager->reveal());

        $this->controllerFactory = new ControllerFactory();
        $this->serviceLocator = $serviceLocator->reveal();
        $this->manager = $manager->reveal();
    }
    
    public function testCreateService()
    {
        $pluginManager = $this->prophesize(AbstractPluginManager::class);
        $pluginManager->getServiceLocator()->shouldBeCalled()->willReturn($this->serviceLocator);

        $factory = new ControllerFactory();
        $service = $factory->createService($pluginManager->reveal());

        static::assertInstanceOf(Controller::class, $service);
        static::assertSame($this->manager, $service->getRpcServerManager());
    }

    public function testInvoke()
    {
        $factory = new ControllerFactory();
        /** @var Controller $service */
        $service = $factory($this->serviceLocator, Controller::class);

        static::assertInstanceOf(Controller::class, $service);
        static::assertSame($this->manager, $service->getRpcServerManager());
    }
}
