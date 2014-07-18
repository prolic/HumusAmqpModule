<?php

namespace HumusAmqpModule\Controller;

use Zend\Console\ColorInterface;
use Zend\Console\Prompt;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class PurgeConsumerController extends AbstractConsoleController
{
    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        $consumerName = $request->getParam('consumer-name');

        if (!$this->getServiceLocator()->has($consumerName)) {
            return $this->getConsole()->writeLine(
                'ERROR: Consumer "' . $consumerName . '" not found',
                ColorInterface::RED
            );
        }

        if (Prompt\Confirm::prompt('Are you sure you want to purge? [y/n]')) {
            $consumer = $this->getServiceLocator()->get($consumerName);
            $consumer->purge();
            return $this->getConsole()->writeLine(
                'OK',
                ColorInterface::GREEN
            );
        } else {
            return $this->getConsole()->writeLine(
                'Purging cancelled!',
                ColorInterface::YELLOW
            );
        }
    }
}
