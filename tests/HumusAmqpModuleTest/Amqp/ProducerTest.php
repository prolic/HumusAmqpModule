<?php

namespace HumusAmqpModuleTest\Amqp;

use HumusAmqpModule\Amqp\Producer;

class ProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testPublish()
    {
        $amqpConnection = $this->getMockBuilder('\PhpAmqpLib\Connection\AMQPConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpChannel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpChannel
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->anything(), '', 'bar');

        $producer = new Producer($amqpConnection, $amqpChannel);

        $producer->setContentType('text/plain');
        $producer->setDeliveryMode(2);
        $producer->disableAutoSetupFabric();

        $producer->publish('foo', 'bar', array('baz' => 'bam'));
    }

    public function testBatchPublish()
    {
        $amqpConnection = $this->getMockBuilder('\PhpAmqpLib\Connection\AMQPConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpChannel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpChannel
            ->expects($this->exactly(2))
            ->method('batch_basic_publish');

        $amqpChannel
            ->expects($this->once())
            ->method('publish_batch');

        $producer = new Producer($amqpConnection, $amqpChannel);

        $producer->publishBasicBatch('foo');
        $msg = $producer->defaultMessage('bar');
        $producer->publishBasicBatchMessage($msg);

        $producer->publishBatch();
    }
}
