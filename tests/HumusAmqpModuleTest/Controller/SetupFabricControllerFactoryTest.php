<?php

namespace HumusAmqpModuleTest\Controller;

use HumusAmqpModule\Controller\SetupFabricController as Controller;
use HumusAmqpModule\Controller\SetupFabricControllerFactory as ControllerFactory;
use HumusAmqpModule\PluginManager\Connection;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class SetupFabricControllerFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ControllerFactory
     */
    protected $controllerFactory;
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;
    /**
     * @var Connection
     */
    protected $manager;
    /**
     * @var array
     */
    protected $config = [
        'humus_amqp_module' => ['foo' => 'bar']
    ];

    public function setUp()
    {
        $manager = $this->prophesize(Connection::class);
        $serviceLocator = $this->prophesize(ServiceLocatorInterface::class);
        $serviceLocator->get(Connection::class)->willReturn($manager->reveal());
        $serviceLocator->get('config')->willReturn($this->config);

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
        static::assertSame($this->manager, $service->getConnectionManager());
    }

    public function testInvoke()
    {
        $factory = new ControllerFactory();
        /** @var Controller $service */
        $service = $factory($this->serviceLocator, Controller::class);

        static::assertInstanceOf(Controller::class, $service);
        static::assertSame($this->manager, $service->getConnectionManager());
    }
}
