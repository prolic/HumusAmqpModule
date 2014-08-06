<?php

namespace HumusAmqpModuleTest\PluginManager;

use HumusAmqpModule\PluginManager\MultipleConsumer as MultipleConsumerPluginManager;

class MultipleConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatePlugin()
    {
        $mock = $this->getMockForAbstractClass('HumusAmqpModule\\Amqp\\MultipleConsumerInterface');
        $manager = new MultipleConsumerPluginManager();
        $manager->validatePlugin($mock);
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     */
    public function testInvalidPlugin()
    {
        $manager = new MultipleConsumerPluginManager();
        $manager->validatePlugin('foo');
    }
}
