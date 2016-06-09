<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace HumusAmqpModuleTest\Amqp;

use HumusAmqpModule\Consumer;
use HumusAmqpModule\ConsumerCallbackInterface;
use HumusAmqpModule\ConsumerInterface;
use HumusAmqpModule\ExceptionCallbackInterface;
use Prophecy\Argument;

/**
 * Class ConsumerTest
 * @package HumusAmqpModuleTest\Amqp
 */
class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Consumer
     */
    protected $consumer;
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|\AMQPChannel
     */
    protected $channelProphet;
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|\AMQPQueue
     */
    protected $queueProphet;

    protected function setUp()
    {
        $this->channelProphet = $this->prophesize(\AMQPChannel::class);
        $this->channelProphet->getPrefetchCount()->shouldBeCalledTimes(1)->willReturn(3);

        $this->queueProphet = $this->prophesize(\AMQPQueue::class);
        $this->queueProphet->getChannel()->willReturn($this->channelProphet->reveal());

        $this->consumer = new Consumer([$this->queueProphet->reveal()], 1, 1 * 1000 * 500);
    }

    public function testProcessMessage()
    {
        $consumer = $this->consumer;

        $message = $this->prophesize(\AMQPEnvelope::class);
        $message->getDeliveryTag()->willReturn(null);
        $message->isRedelivery()->willReturn(false);

        $this->queueProphet->get()->willReturn($message->reveal());

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->debug(Argument::any());
        $logger->error(Argument::any());
        $consumer->setLogger($logger->reveal());

        // Create a callback function with a return value set by the data provider.
        $callbackFunction = function () {
            static $i = 0;
            $i++;
            switch ($i) {
                case 1:
                    return;
                case 2:
                    return;
                case 3:
                    return;
                case 4:
                    return ConsumerInterface::MSG_ACK;
                case 5:
                    return false;
                case 6:
                    return ConsumerInterface::MSG_REJECT;
                case 7:
                    return ConsumerInterface::MSG_REJECT_REQUEUE;
                case 8:
                    return true;
            }
        };
        $consumer->setDeliveryCallback($callbackFunction);

        $this->queueProphet->ack(Argument::any(), Argument::any())->shouldBeCalledTimes(2);
        $this->queueProphet->reject(Argument::any(), Argument::any())->shouldBeCalledTimes(3);

        $consumer->consume(7);
    }

    public function testFlushDeferred()
    {
        $consumer = $this->consumer;

        $message = $this->prophesize(\AMQPEnvelope::class);
        $message->getDeliveryTag()->will(function () {
            return uniqid('', true);
        });
        $message->isRedelivery()->willReturn(false);

        $this->queueProphet->get()->willReturn($message->reveal());

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->debug(Argument::any());
        $logger->error(Argument::any());
        $consumer->setLogger($logger->reveal());

        // Create a callback function with a return value set by the data provider.
        $callbackFunction = function () {
            static $i = 0;
            $i++;
            switch ($i) {
                case 1:
                    return;
                case 2:
                    return;
                case 3:
                    return;
                case 4:
                    return ConsumerInterface::MSG_ACK;
                case 5:
                    return false;
                case 6:
                    return ConsumerInterface::MSG_REJECT;
                case 7:
                    return ConsumerInterface::MSG_REJECT_REQUEUE;
                case 8:
                    return true;
            }
        };
        $consumer->setDeliveryCallback($callbackFunction);
        $consumer->setFlushCallback(function () {
            static $i = 0;
            $i++;
            if ($i == 1) {
                return true;
            }
            return false;
        });

        $this->queueProphet->ack(Argument::any(), Argument::any())->shouldBeCalledTimes(3);
        $this->queueProphet->reject(Argument::any(), Argument::any())->shouldBeCalledTimes(3);

        $consumer->consume(5);
    }

    public function testHandleDeliveryWithCallbackInterface()
    {
        $consumer = $this->consumer;

        $envelope = $this->prophesize(\AMQPEnvelope::class);

        $callback = $this->prophesize(ConsumerCallbackInterface::class);
        $callback->onMessage($envelope->reveal(), $this->queueProphet->reveal(), $consumer)->shouldBeCalledTimes(1);

        $consumer->setDeliveryCallback($callback->reveal());
        $consumer->handleDelivery($envelope->reveal(), $this->queueProphet->reveal());
    }

    public function testHandleDeliveryException()
    {
        $consumer = $this->consumer;

        $exception = new \Exception('Test Exception');
        $errorCallback = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $errorCallback->expects(static::once())
            ->method('__invoke')
            ->with($exception, $consumer);
        $consumer->setErrorCallback($errorCallback);
        $consumer->handleDeliveryException($exception);
    }

    public function testHandleDeliveryExceptionWithLogger()
    {
        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->error(Argument::any())->shouldBeCalled();

        $exception = new \Exception('Test Exception');

        $consumer = $this->consumer;
        $consumer->setLogger($logger->reveal());
        $consumer->handleDeliveryException($exception);
    }

    public function testHandleDeliveryExceptionWithCallbackInterface()
    {
        $consumer = $this->consumer;

        $exception = new \Exception('Test Exception');
        $errorCallback = $this->prophesize(ExceptionCallbackInterface::class);
        $errorCallback->onDeliveryException($exception, $consumer)->shouldBeCalledTimes(1);

        $consumer->setErrorCallback($errorCallback->reveal());
        $consumer->handleDeliveryException($exception);
    }

    public function testHandleFlushDeferredException()
    {
        $consumer = $this->consumer;

        $exception = new \Exception('Test Exception');
        $errorCallback = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $errorCallback->expects(static::once())
            ->method('__invoke')
            ->with($exception, $consumer);

        $consumer->setErrorCallback($errorCallback);
        $consumer->handleFlushDeferredException($exception);
    }

    public function testHandleFlushDeferredExceptionWithLogger()
    {
        $consumer = $this->consumer;
        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->error(Argument::any())->shouldBeCalled();

        $exception = new \Exception('Test Exception');

        $consumer->setLogger($logger->reveal());
        $consumer->handleFlushDeferredException($exception);
    }

    public function testHandleFlushDeferredExceptionWithCallbackInterface()
    {
        $consumer = $this->consumer;

        $exception = new \Exception('Test Exception');
        $errorCallback = $this->prophesize(ExceptionCallbackInterface::class);
        $errorCallback->onFlushDeferredException($exception, $consumer)->shouldBeCalledTimes(1);

        $consumer->setErrorCallback($errorCallback->reveal());
        $consumer->handleFlushDeferredException($exception);
    }
}
