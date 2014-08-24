<?php

namespace HumusAmqpModule\Service;

use HumusAmqp\Factory\Exchange as ExchangeFactory;
use HumusAmqp\ExchangeSpecification;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExchangeAbstractServiceFactory extends AbstractAmqpAbstractServiceFactory
{
    /**
     * @var ExchangeFactory
     */
    protected $exchangeFactory;

    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'exchanges';

    /**
     * Constructor
     *
     * @param ExchangeFactory $factory
     */
    public function __construct(ExchangeFactory $factory = null)
    {
        if (null === $factory) {
            $factory = new ExchangeFactory();
        }
        $this->exchangeFactory = $factory;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        // get global service locator, if we are in a plugin manager
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $config  = $this->getConfig($serviceLocator);

        $spec = $config[$this->subConfigKey][$requestedName];
        $spec = new ExchangeSpecification($spec);

        $this->exchangeFactory->create($spec);
    }
}
