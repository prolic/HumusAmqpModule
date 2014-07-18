<?php

namespace HumusAmqpModule\Controller;

use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class RpcServerController extends AbstractConsoleController
{
    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        $rpcServerName = $request->getParam('name');

        if (!$this->getServiceLocator()->has($rpcServerName)) {
            return $this->getConsole()->writeLine(
                'ERROR: RPC-Server "' . $rpcServerName . '" not found',
                ColorInterface::RED
            );
        }

        $debug = $request->getParam('debug') || $request->getParam('d');

        if ($debug && !defined('AMQP_DEBUG')) {
            define('AMQP_DEBUG', true);
        }

        $amount =$amount = $request->getParam('amount', 0);

        if (!is_numeric($amount)) {
            return $this->getConsole()->writeLine(
                'Error: amount should be null or greater than 0',
                ColorInterface::RED
            );
        }

        $rpcServer = $this->getServiceLocator()->get($rpcServerName);
        $rpcServer->start($amount);
    }
}
