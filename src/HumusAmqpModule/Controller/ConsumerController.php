<?php

namespace HumusAmqpModule\Controller;

use HumusAmqpModule\Exception;
use HumusAmqpModule\Amqp\Consumer;
use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class ConsumerController extends AbstractConsoleController
{

    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        if (false === defined('AMQP_WITHOUT_SIGNALS')) {
            define('AMQP_WITHOUT_SIGNALS', $request->getParam('without-signals'));
        }

        if (!AMQP_WITHOUT_SIGNALS && extension_loaded('pcntl')) {
            if (!function_exists('pcntl_signal')) {
                throw new Exception\BadFunctionCallException("Function 'pcntl_signal' is referenced in the php.ini 'disable_functions' and can't be called.");
            }

            pcntl_signal(SIGTERM, array(&$this, 'stopConsumer'));
            pcntl_signal(SIGINT, array(&$this, 'stopConsumer'));
            pcntl_signal(SIGHUP, array(&$this, 'restartConsumer'));
        }

        if (!defined('AMQP_DEBUG') && ($request->getParam('debug') || $request->getParam('d'))) {
            define('AMQP_DEBUG', true);
        }

        if (!$this->consumer = $this->loadConsumer($this->getServiceLocator(), $request->getParam('name'))) {
            return null;
        }

        $this->consumer->setMemoryLimit($request->getParam('memory_limit'));
        $this->consumer->setRoutingKey($request->getParam('route'));

        $amount = $request->getParam('amount', 0);

        if (!is_numeric($amount)) {
            return $this->getConsole()->writeLine('Error: amount should be null or greater than 0', ColorInterface::RED);
        }

        $this->consumer->consume($amount);
    }

    protected function loadConsumer(ServiceLocatorInterface $serviceLocator, $name)
    {
        if (!$serviceLocator->has($name)) {
            $this->getConsole()->writeLine('Error: unknown consumer "' . $name .'"', ColorInterface::RED);
            return null;
        }

        $consumer = $serviceLocator->get($name);

        if (!$consumer instanceof Consumer) {
            $this->getConsole()->writeLine('Error: "' . $name. '" is not a consumer', ColorInterface::RED);
            return null;
        }

        return $consumer;
    }

    public function stopConsumer()
    {
        if ($this->consumer instanceof Consumer) {
            $this->consumer->forceStopConsumer();
        } else {
            exit();
        }
    }

    public function restartConsumer()
    {
        // TODO: Implement restarting of consumer
    }
}
