<?php

namespace HumusAmqpModule\Service\Controller;

use HumusAmqpModule\Controller\ConsumerManagerAwareInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractConsumerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $class = $this->getControllerClass();
        $controller = new $class();
        return $this->injectConsumerPluginManager($controller, $sm);
    }

    /**
     * @return string
     */
    abstract protected function getConsumerType();

    /**
     * @return string
     */
    abstract protected function getControllerClass();

    /**
     * @param ConsumerManagerAwareInterface $controller
     * @param ServiceLocatorInterface $serviceLocator
     * @return ConsumerManagerAwareInterface
     */
    protected function injectConsumerPluginManager(
        ConsumerManagerAwareInterface $controller,
        ServiceLocatorInterface $serviceLocator
    ) {
        $manager = $serviceLocator->get($this->getConsumerType());
        $controller->setConsumerManager($manager);
        return $controller;
    }
}
