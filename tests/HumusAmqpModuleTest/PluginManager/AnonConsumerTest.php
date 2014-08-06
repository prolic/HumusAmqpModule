<?php

namespace HumusAmqpModuleTest\PluginManager;

use HumusAmqpModule\PluginManager\AnonConsumer as AnonConsumerPluginManager;

class AnonConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatePlugin()
    {
        $options = $this->getMock('HumusAmqpModule\\Amqp\\QueueOptions');
        $options
            ->expects($this->once())
            ->method('getName')
            ->willReturn(null);

        $mock = $this->getMockForAbstractClass('HumusAmqpModule\\Amqp\\ConsumerInterface');
        $mock
            ->expects($this->once())
            ->method('getQueueOptions')
            ->willReturn($options);

        $manager = new AnonConsumerPluginManager();
        $manager->validatePlugin($mock);
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     */
    public function testInvalidPlugin()
    {
        $manager = new AnonConsumerPluginManager();
        $manager->validatePlugin('foo');
    }
}
