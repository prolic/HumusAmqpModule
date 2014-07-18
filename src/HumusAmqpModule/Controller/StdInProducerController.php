<?php

namespace HumusAmqpModule\Controller;

use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class StdInProducerController extends AbstractConsoleController
{
    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        $producerName = $request->getParam('name');
        $producer = $this->getServiceLocator()->get($producerName);

        $msg = trim($request->getParam('msg'));

        $producer->publish($msg);
    }
}
