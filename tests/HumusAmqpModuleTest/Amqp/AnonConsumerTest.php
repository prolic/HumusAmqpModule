<?php

namespace HumusAmqpModuleTest\Amqp;

use HumusAmqpModule\Amqp\AnonConsumer;

class AnonConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testAnonConsumerHasNoQueueName()
    {
        $mock = $this->getMock('PhpAmqpLib\Connection\AMQPLazyConnection', array(), array(), '', false);
        $anonConsumer = new AnonConsumer($mock);
        $this->assertNull($anonConsumer->getQueueOptions()->getName());
    }
}
