<?php

namespace HumusAmqpModuleTest;

use HumusAmqpModule\RpcServer;
use Mockery as m;

class RpcServerTest extends \PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
        m::close();
    }

    public function testProcessMessage()
    {
        $amqpChannel = $this->getMockBuilder('AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpChannel->expects($this->once())->method('getPrefetchCount')->willReturn(3);

        $message = $this->getMockBuilder('AMQPEnvelope')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpQueue = $this->getMockBuilder('AMQPQueue')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpExchange = $this->getMockBuilder('AMQPExchange')
            ->disableOriginalConstructor()
            ->getMock();

        $eventResponse = $this->getMock('Zend\EventManager\ResponseCollection');

        $eventManager = m::mock('Zend\EventManager\EventManager');
        $eventManager->shouldReceive('setIdentifiers');

        $message->expects($this->once())->method('getDeliveryTag')->willReturn('delivery-tag');
        $message->expects($this->once())->method('getCorrelationId')->willReturn('correlation-id');
        $message->expects($this->once())->method('getReplyTo')->willReturn('reply-to');

        $amqpQueue->expects($this->once())->method('getChannel')->willReturn($amqpChannel);
        $amqpQueue->expects($this->any())->method('get')->willReturn($message);


        $reponse = json_encode(array('success' => true, 'result' => 'response-result'));
        $amqpExchange->expects($this->once())->method('publish')
            ->with(
                $this->equalTo($reponse),
                $this->equalTo('reply-to'),
                $this->equalTo(AMQP_NOPARAM),
                $this->callback(
                    function ($param) {
                        return is_array($param);
                    }
                )
            );

        $rpcServer = new RpcServer($amqpQueue, 1, 1 * 1000 * 500);
        $rpcServer->setEventManager($eventManager);
        $rpcServer->setExchange($amqpExchange);

        $eventResponse->expects($this->atLeast(1))->method('last')->willReturn('response-result');
        $eventManager->shouldReceive('trigger')
            ->once()
            ->with('delivery', $rpcServer, ['message' => $message, 'queue' => $amqpQueue])
            ->andReturn($eventResponse);

        $eventManager->shouldReceive('trigger')
            ->once()
            ->with('ack', $rpcServer, m::any());

        $amqpQueue->expects($this->once())->method('ack');

        $rpcServer->consume(1);
    }
}
