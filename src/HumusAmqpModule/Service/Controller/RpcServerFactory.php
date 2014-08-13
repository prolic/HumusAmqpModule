<?php

namespace HumusAmqpModule\Service\Controller;

use HumusAmqpModule\Controller\RpcServerController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RpcServerFactory implements FactoryInterface
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
        $rpcServerManager = $sm->get('HumusAmqpModule\PluginManager\RpcServer');

        $controller = new RpcServerController();
        $controller->setRpcServerManager($rpcServerManager);

        return $controller;
    }
}
