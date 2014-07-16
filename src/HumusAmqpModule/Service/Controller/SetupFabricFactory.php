<?php

namespace HumusAmqpModule\Service\Controller;

use HumusAmqpModule\Controller\SetupFabricController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SetupFabricFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $controller  = new SetupFabricController();
        $partsHolder = $serviceLocator->getServiceLocator()->get('HumusAmqpModule\Amqp\PartsHolder');
        $controller->setPartsHolder($partsHolder);

        return $controller;
    }
}
