<?php

namespace HumusAmqpModuleTest\PluginManager;

use HumusAmqpModule\PluginManager\Consumer as ConsumerPluginManager;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatePlugin()
    {
        $mock = $this->getMockForAbstractClass('HumusAmqpModule\\Amqp\\ConsumerInterface');
        $manager = new ConsumerPluginManager();
        $manager->validatePlugin($mock);
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     */
    public function testInvalidPlugin()
    {
        $manager = new ConsumerPluginManager();
        $manager->validatePlugin('foo');
    }
}
