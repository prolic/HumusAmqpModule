<?php

namespace HumusAmqpModule\Service;

use HumusAmqpModule\Amqp\PartsHolder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PartsHolderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return PartsHolder
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $moduleConfig = $config['humus_amqp_module'];

        $partsHolder = new PartsHolder();

        foreach ($moduleConfig as $key => $value) {
            if (in_array($key, array('connections', 'classes'))) continue;

            foreach ($value as $name => $producer) {
                $partsHolder->addPart($key, $serviceLocator->get($name));
            };
        }

        return $partsHolder;
    }
}
