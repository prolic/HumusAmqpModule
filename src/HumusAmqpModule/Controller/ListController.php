<?php

namespace HumusAmqpModule\Controller;

use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class ListController extends AbstractConsoleController
{
    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        $type = $this->getRequest()->getParam('type');

        $config = $this->getServiceLocator()->get('Config');
        $moduleConfig = $config['humus_amqp_module'];

        $this->getConsole()->writeLine('List of all available ' . $type, ColorInterface::GREEN);

        if (!isset($moduleConfig[$type])) {
            return $this->getConsole()->writeLine('No ' . $type . ' found', ColorInterface::RED);
        }

        $list = array_keys($moduleConfig[$type]);



        foreach ($list as $entry) {
            $this->getConsole()->writeLine($entry);
        }
    }
}
