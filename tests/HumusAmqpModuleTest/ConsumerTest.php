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
use HumusAmqpModule\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumerTest extends \PHPUnit_Framework_TestCase
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

        $amqpQueue->expects($this->once())->method('getChannel')->willReturn($amqpChannel);
        $amqpQueue->expects($this->any())->method('get')->willReturn($message);

        $consumer = new Consumer(array($amqpQueue));


        $logger = new \Zend\Log\Logger();
        $writers = new \Zend\Stdlib\SplPriorityQueue();
        $writers->insert(new \Zend\Log\Writer\Null(), 0);
        $logger->setWriters($writers);
        $consumer->setLogger($logger);

        // Create a callback function with a return value set by the data provider.
        $callbackFunction = function () {
            static $i = 0;
            $i++;
            switch ($i) {
                case 1:
                    return null;
                case 2:
                    return null;
                case 3:
                    return null;
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

        $amqpQueue->expects($this->exactly(2))->method('ack');
        $amqpQueue->expects($this->exactly(3))->method('reject');

        $consumer->consume(7);
    }

    public function testFlushDeferred()
    {
        $amqpChannel = $this->getMockBuilder('AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpChannel->expects($this->once())->method('getPrefetchCount')->willReturn(3);

        $message = $this->getMockBuilder('AMQPEnvelope')
            ->disableOriginalConstructor()
            ->getMock();

        $message->expects($this->any())->method('getDeliveryTag')->willReturnCallback(function() {
            return uniqid();
        });

        $amqpQueue = $this->getMockBuilder('AMQPQueue')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpQueue->expects($this->once())->method('getChannel')->willReturn($amqpChannel);
        $amqpQueue->expects($this->any())->method('get')->willReturn($message);

        $consumer = new Consumer(array($amqpQueue));


        $logger = new \Zend\Log\Logger();
        $writers = new \Zend\Stdlib\SplPriorityQueue();
        $writers->insert(new \Zend\Log\Writer\Null(), 0);
        $logger->setWriters($writers);
        $consumer->setLogger($logger);

        // Create a callback function with a return value set by the data provider.
        $callbackFunction = function () {
            static $i = 0;
            $i++;
            switch ($i) {
                case 1:
                    return null;
                case 2:
                    return null;
                case 3:
                    return null;
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
        $consumer->setFlushCallback(function() {
            static $i = 0;
            $i++;
            if ($i == 1) {
                return true;
            }
            return false;
        });

        $amqpQueue->expects($this->exactly(3))->method('ack');
        $amqpQueue->expects($this->exactly(3))->method('reject');

        $consumer->consume(5);
    }

}
