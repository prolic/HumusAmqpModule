<?php

namespace HumusAmqpModule\Controller;

use Zend\ServiceManager\ServiceLocatorInterface;

interface ConsumerManagerAwareInterface
{
    /**
     * @param ServiceLocatorInterface $manager
     */
    public function setConsumerManager(ServiceLocatorInterface $manager);

    /**
     * @return ServiceLocatorInterface
     */
    public function getConsumerManager();
}
