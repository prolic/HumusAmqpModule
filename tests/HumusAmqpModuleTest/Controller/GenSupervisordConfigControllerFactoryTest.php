<?php

namespace HumusAmqpModuleTest\Controller;

use HumusAmqpModule\Controller\GenSupervisordConfigController as Controller;
use HumusAmqpModule\Controller\GenSupervisordConfigControllerFactory as ControllerFactory;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceManager;

class GenSupervisordConfigControllerFactoryTest extends \PHPUnit_Framework_TestCase
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
     * @var array
     */
    protected $defaultConfig = [
        'humus_supervisor_module' => [
            'humus-amqp-supervisor' => [
                'supervisord' => ['foo' => 'bar']
            ]
        ],
        'humus_amqp_module' => ['foo' => 'bar']
    ];

    protected function setUp()
    {
        $serviceLocator = $this->prophesize(ServiceManager::class);

        $serviceLocator->get('config')
            ->willReturn($this->defaultConfig);

        $this->controllerFactory = new ControllerFactory();
        $this->serviceLocator = $serviceLocator->reveal();
    }


    public function testCreateService()
    {
        $pluginManager = $this->prophesize(AbstractPluginManager::class);

        $pluginManager->getServiceLocator()->shouldBeCalled()->willReturn($this->serviceLocator);

        $service = $this->controllerFactory->createService($pluginManager->reveal());

        static::assertInstanceOf(Controller::class, $service);
    }

    public function testInvoke()
    {
        $factory = $this->controllerFactory;
        $service = $factory($this->serviceLocator, Controller::class);

        static::assertInstanceOf(Controller::class, $service);
    }
}
