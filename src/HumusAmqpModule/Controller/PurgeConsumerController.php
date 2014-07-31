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

        /* @var $request \Zend\Console\Request */

        $consumerName = $request->getParam('consumer-name');

        if (!$this->getServiceLocator()->has($consumerName)) {
            $this->getConsole()->writeLine(
                'ERROR: Consumer "' . $consumerName . '" not found',
                ColorInterface::RED
            );
            return null;
        }

        if ($request->getParam('no-confirmation', false)
            || Prompt\Confirm::prompt('Are you sure you want to purge? [y/n]')
        ) {
            $consumer = $this->getServiceLocator()->get($consumerName);
            $consumer->purge();
            $this->getConsole()->writeLine(
                'OK',
                ColorInterface::GREEN
            );
        } else {
            $this->getConsole()->writeLine(
                'Purging cancelled!',
                ColorInterface::YELLOW
            );
        }
    }
}
