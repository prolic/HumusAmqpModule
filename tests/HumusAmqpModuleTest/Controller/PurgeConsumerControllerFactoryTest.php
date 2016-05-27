<?php

namespace HumusAmqpModuleTest\Controller;

use HumusAmqpModule\Controller\PurgeConsumerController as Controller;
use HumusAmqpModule\Controller\PurgeConsumerControllerFactory as ControllerFactory;
use HumusAmqpModule\PluginManager\Consumer;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class PurgeConsumerControllerFactoryTest extends \PHPUnit_Framework_TestCase
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
     * @var Consumer
     */
    protected $manager;

    public function setUp()
    {
        $manager = $this->prophesize(Consumer::class);
        $serviceLocator = $this->prophesize(ServiceLocatorInterface::class);
        $serviceLocator->get(Consumer::class)->willReturn($manager->reveal());

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
        static::assertSame($this->manager, $service->getConsumerManager());
    }

    public function testInvoke()
    {
        $factory = new ControllerFactory();
        /** @var Controller $service */
        $service = $factory($this->serviceLocator, Controller::class);

        static::assertInstanceOf(Controller::class, $service);
        static::assertSame($this->manager, $service->getConsumerManager());
    }
}
