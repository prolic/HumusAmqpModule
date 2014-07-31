<?php

namespace HumusAmqpModuleTest\Amqp;

use HumusAmqpModule\Amqp\Consumer;
use PhpAmqpLib\Connection\AMQPLazyConnection;

class AbstractAmqpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function testLazyConnection()
    {
        $amqpLazyConnection = new AMQPLazyConnection('localhost', 123, 'lazy_user', 'lazy_password');

        $consumer = new Consumer($amqpLazyConnection, null);
        $consumer->getChannel();
    }
}
