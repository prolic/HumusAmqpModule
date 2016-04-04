<?php

namespace HumusAmqpModuleTest;

use HumusAmqpModule\RpcServer;

/**
 * Class RpcServerTest
 * @package HumusAmqpModuleTest
 */
class RpcServerTest extends \PHPUnit_Framework_TestCase
{
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

        $message->expects($this->once())->method('getDeliveryTag')->willReturn('delivery-tag');
        $message->expects($this->once())->method('getCorrelationId')->willReturn('correlation-id');
        $message->expects($this->once())->method('getReplyTo')->willReturn('reply-to');

        $amqpQueue->expects($this->once())->method('getChannel')->willReturn($amqpChannel);
        $amqpQueue->expects($this->any())->method('get')->willReturn($message);

        $reponse = json_encode(['success' => true, 'result' => 'response-result']);
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
        $rpcServer->setExchange($amqpExchange);
        $rpcServer->setDeliveryCallback(function () {
            return 'response-result';
        });

        $logger = new \Zend\Log\Logger();
        $writers = new \Zend\Stdlib\SplPriorityQueue();
        $writers->insert(new \Zend\Log\Writer\Noop(), 0);
        $logger->setWriters($writers);
        $rpcServer->setLogger($logger);

        $amqpQueue->expects($this->once())->method('ack');

        $rpcServer->consume(1);
    }
}
