<?php

namespace HumusAmqpModuleTest\Listener;

use Mockery as m;
use HumusAmqpModule\Listener\LoggerListener;

class LoggerListenerTest extends \PHPUnit_Framework_TestCase
{

    public function testAttach()
    {
        $eventManagerMock = m::mock('Zend\EventManager\EventManagerInterface');
        $callbackHandlerMock = m::mock('Zend\Stdlib\CallbackHandler');
        $loggerMock = m::mock('Zend\Log\LoggerInterface');
        $loggerListener = new LoggerListener($loggerMock);

        $eventManagerMock->shouldReceive('attach')
            ->once()
            ->with('ack', [$loggerListener, 'onAck'])
            ->andReturn($callbackHandlerMock);

        $eventManagerMock->shouldReceive('attach')
            ->once()
            ->with('deliveryException', [$loggerListener, 'onDeliveryException'])
            ->andReturn($callbackHandlerMock);

        $eventManagerMock->shouldReceive('attach')
            ->once()
            ->with('flushDeferredException', [$loggerListener, 'onFlushDeferredException'])
            ->andReturn($callbackHandlerMock);

        $loggerListener->attach($eventManagerMock);
    }

    public function testOnAck()
    {
        $eventMock = m::mock('Zend\EventManager\EventInterface');
        $eventMock->shouldReceive('getParam')->once()->with('timestampLastMessage')->andReturn(100);
        $eventMock->shouldReceive('getParam')->once()->with('timestampLastAck')->andReturn(50);
        $eventMock->shouldReceive('getParam')->once()->with('countMessagesUnacked')->andReturn(100);

        $loggerMock = m::mock('Zend\Log\LoggerInterface');
        $loggerMock->shouldReceive('debug')
            ->once()
            ->with(sprintf(
                'Acknowledged %d messages at %.0f msg/s',
                100,
                2
            ));

        $loggerListener = new LoggerListener($loggerMock);

        $loggerListener->onAck($eventMock);
    }

    public function testOnDeliveryException()
    {
        $exceptionMessage = 'Error message';
        $exception = new \Exception($exceptionMessage);

        $eventMock = m::mock('Zend\EventManager\EventInterface');
        $eventMock->shouldReceive('getParam')->once()->with('exception')->andReturn($exception);

        $loggerMock = m::mock('Zend\Log\LoggerInterface');
        $loggerMock->shouldReceive('err')
            ->once()
            ->with(sprintf('Exception during handleDelivery: %s', $exceptionMessage));

        $loggerListener = new LoggerListener($loggerMock);

        $loggerListener->onDeliveryException($eventMock);
    }

    public function testOnFlushDeferredException()
    {
        $exceptionMessage = 'Error message';
        $exception = new \Exception($exceptionMessage);

        $eventMock = m::mock('Zend\EventManager\EventInterface');
        $eventMock->shouldReceive('getParam')->once()->with('exception')->andReturn($exception);

        $loggerMock = m::mock('Zend\Log\LoggerInterface');
        $loggerMock->shouldReceive('err')
            ->once()
            ->with(sprintf('Exception during flushDeferred: %s', $exceptionMessage));

        $loggerListener = new LoggerListener($loggerMock);

        $loggerListener->onFlushDeferredException($eventMock);
    }

    protected function tearDown()
    {
        m::close();
    }
}
