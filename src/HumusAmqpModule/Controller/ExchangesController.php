<?php

namespace HumusAmqpModule\Controller;

use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class ExchangesController extends AbstractConsoleController
{
    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        $config = $this->getServiceLocator()->get('Config');
        $moduleConfig = $config['humus_amqp_module'];

        $this->getConsole()->writeLine('List of all exchanges', ColorInterface::GREEN);

        $exchanges = array();
        foreach ($moduleConfig as $type) {
            foreach ($type as $configPart) {
                if (!is_array($configPart)) continue;
                foreach ($configPart as $key => $value) {
                    if ($key == 'exchange_options') {
                        $exchanges[$value['type']][] = $value['name'];
                    }
                }
            }
        }

        foreach ($exchanges as $type => $values) {
            $this->getConsole()->writeLine('Exchange-Type: ' . $type, ColorInterface::GREEN);
            foreach ($values as $value) {
                $this->getConsole()->writeLine($value);
            }
        }
    }
}
