<?php

namespace HumusAmqpModule\Service\Controller;

use HumusAmqpModule\Controller\StdInProducerController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StdInProducerFactory implements FactoryInterface
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
        $controller = new StdInProducerController();
        $controller->setProducerManager($sm->get('HumusAmqpModule\\PluginManager\\Producer'));
        return $controller;
    }
}
