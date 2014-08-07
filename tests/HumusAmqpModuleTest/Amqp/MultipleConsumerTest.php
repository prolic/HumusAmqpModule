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

use HumusAmqpModule\Amqp\ConsumerInterface;
use HumusAmqpModule\Amqp\MultipleConsumer;
use PhpAmqpLib\Message\AMQPMessage;

class MultipleConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException HumusAmqpModule\Amqp\Exception\QueueNotFoundException
     */
    public function testProcessMessageWithInvalidQueueName()
    {
        $amqpConnection = $this->getMockBuilder('\PhpAmqpLib\Connection\AMQPConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpChannel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $consumer = new MultipleConsumer($amqpConnection, $amqpChannel);
        $consumer->processQueueMessage('foo', new AMQPMessage('foo body'));
    }

    /**
     * Check if the message is requeued or not correctly.
     *
     * @dataProvider processMessageProvider
     */
    public function testProcessMessage($processFlag, $expectedMethod, $expectedRequeue = null)
    {
        $amqpConnection = $this->getMockBuilder('\PhpAmqpLib\Connection\AMQPConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpChannel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $consumer = new MultipleConsumer($amqpConnection, $amqpChannel);
        $callback = function () use (&$lastQueue, $processFlag) {
            return $processFlag;
        };

        $consumer->setQueues(array(
            'test-1' => array(
                'callback' => $callback
            ),
            'test-2'  => array(
                'callback' => $callback
            )
        ));

        // Create a default message
        $amqpMessage = new AMQPMessage('foo body');
        $amqpMessage->delivery_info['channel'] = $amqpChannel;
        $amqpMessage->delivery_info['delivery_tag'] = 0;
        $amqpChannel->expects($this->any())
            ->method('basic_reject')
            ->will($this->returnCallback(function ($delivery_tag, $requeue) use ($expectedMethod, $expectedRequeue) {
                \PHPUnit_Framework_Assert::assertSame($expectedMethod, 'basic_reject');
                \PHPUnit_Framework_Assert::assertSame($requeue, $expectedRequeue);
            }));

        $amqpChannel->expects($this->any())
            ->method('basic_ack')
            ->will($this->returnCallback(function ($delivery_tag) use ($expectedMethod) {
                \PHPUnit_Framework_Assert::assertSame($expectedMethod, 'basic_ack');
            }));

        $consumer->processQueueMessage('test-1', $amqpMessage);
        $consumer->processQueueMessage('test-2', $amqpMessage);
    }

    /**
     * @expectedException HumusAmqpModule\Amqp\Exception\InvalidArgumentException
     */
    public function testSetQueuesWithInvalidData()
    {
        $amqpConnection = $this->getMockBuilder('\PhpAmqpLib\Connection\AMQPConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpChannel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $consumer = new MultipleConsumer($amqpConnection, $amqpChannel);

        $consumer->setQueues('foobar');
    }

    public function processMessageProvider()
    {
        return array(
            array(null, 'basic_ack'),
            array(true, 'basic_ack'),
            array(false, 'basic_reject', true),
            array(ConsumerInterface::MSG_ACK, 'basic_ack'),
            array(ConsumerInterface::MSG_REJECT_REQUEUE, 'basic_reject', true),
            array(ConsumerInterface::MSG_REJECT, 'basic_reject', false),
        );
    }
}
