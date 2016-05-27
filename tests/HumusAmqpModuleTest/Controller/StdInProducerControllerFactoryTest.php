<?php

namespace HumusAmqpModuleTest\Controller;

use HumusAmqpModule\Controller\StdInProducerController as Controller;
use HumusAmqpModule\Controller\StdInProducerControllerFactory as ControllerFactory;
use HumusAmqpModule\PluginManager\Producer;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceManager;

class StdInProducerControllerFactoryTest extends \PHPUnit_Framework_TestCase
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
     * @var Producer
     */
    protected $manager;

    public function setUp()
    {
        $manager = $this->prophesize(Producer::class);
        $serviceLocator = $this->prophesize(ServiceManager::class);
        $serviceLocator->get(Producer::class)->willReturn($manager->reveal());

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
        static::assertSame($this->manager, $service->getProducerManager());
    }

    public function testInvoke()
    {
        $factory = new ControllerFactory();
        /** @var Controller $service */
        $service = $factory($this->serviceLocator, Controller::class);

        static::assertInstanceOf(Controller::class, $service);
        static::assertSame($this->manager, $service->getProducerManager());
    }
}
