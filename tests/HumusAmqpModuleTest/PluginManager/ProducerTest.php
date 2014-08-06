<?php

namespace HumusAmqpModuleTest\PluginManager;

use HumusAmqpModule\PluginManager\Producer as ProducerPluginManager;

class ProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatePlugin()
    {
        $mock = $this->getMock('HumusAmqpModule\\Amqp\\Producer', array(), array(), '', false);
        $manager = new ProducerPluginManager();
        $manager->validatePlugin($mock);
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     */
    public function testInvalidPlugin()
    {
        $manager = new ProducerPluginManager();
        $manager->validatePlugin('foo');
    }
}
