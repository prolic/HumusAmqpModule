<?php

namespace HumusAmqpModuleTest\Service;

use HumusAmqpModuleTest\ServiceManagerTestCase;
use Zend\ServiceManager\ServiceManager;

class PartsHolderFactoryTest extends ServiceManagerTestCase
{
    public function testCreateService()
    {
        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $config = $serviceManager->get('Config');
        $config['humus_amqp_module']['producers'] = array(
            'test-producer' => array(
                'connection' => 'default'
            )
        );
        $config['humus_amqp_module']['connections'] = array(
            'default' => array(
                'host' => 'localhost',
                'port' => 5672,
                'user' => 'guest',
                'password' => 'guest',
                'vhost' => '/',
                'lazy' => true
            )
        );
        $serviceManager->setService('Config', $config);
        $partsHolder = $serviceManager->get('HumusAmqpModule\Amqp\PartsHolder');

        $this->assertInstanceOf('HumusAmqpModule\Amqp\PartsHolder', $partsHolder);
        $this->assertTrue($partsHolder->hasParts('producers'));
        $this->assertFalse($partsHolder->hasParts('invalid stuff'));
        $parts = $partsHolder->getParts('producers');
        $this->assertInternalType('array', $parts);
        $this->assertCount(1, $parts);
    }
}

