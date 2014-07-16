<?php

namespace HumusAmqpModule\Service\Controller;

use HumusAmqpModule\Controller\SupervisorController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SupervisorFactory implements FactoryInterface
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
        $supervisor = $sm->get('humus-amqp-supervisor');

        $controller = new SupervisorController();
        $controller->setSupervisor($supervisor);

        return $controller;
    }
}
