<?php

namespace HumusAmqpModule\Controller;

use HumusAmqpModule\Amqp\PartsHolder;
use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class SetupFabricController extends AbstractConsoleController
{
    /**
     * @var PartsHolder
     */
    protected $partsHolder;

    /**
     * @param PartsHolder $partsHolder
     */
    public function setPartsHolder(PartsHolder $partsHolder)
    {
        $this->partsHolder = $partsHolder;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        $debug = $request->getParam('debug') || $request->getParam('d');

        if ($debug && !defined('AMQP_DEBUG')) {
            define('AMQP_DEBUG', true);
        }

        $this->console->writeLine('Setting up the RabbitMQ fabric', ColorInterface::RED);

        array_map(
            function($name) {
                foreach ($this->partsHolder->getParts($name) as $part) {
                    $this->console->write('Declaring exchanges and queues for ' . $name);
                    $part->setupFabric();
                    $this->console->writeLine(' OK', ColorInterface::GREEN);
                }
            }, array(
                'consumers',
                'multiple_consumers',
                'anon_consumers',
                'rpc_servers',
                'producers'
            )
        );

        $this->console->writeLine('DONE', ColorInterface::GREEN);
    }
}
