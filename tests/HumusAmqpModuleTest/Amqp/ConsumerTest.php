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
use PhpAmqpLib\Message\AMQPMessage;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check if the message is requeued or not correctly.
     *
     * @dataProvider processMessageProvider
     */
    public function testProcessMessage($processFlag, $expectedMethod, $expectedRequeue = null)
    {
        $amqpConnection = $this->getMockBuilder('AMQPConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $amqpChannel = $this->getMockBuilder('AMQPChannel')
            ->disableOriginalConstructor()
            ->getMock();

        $consumer = new Consumer($amqpConnection, $amqpChannel);

        // Create a callback function with a return value set by the data provider.
        $callbackFunction = function () use ($processFlag) {
            return $processFlag;
        };
        $consumer->setDeliveryCallback($callbackFunction);

        // Create a default message
        $amqpMessage = $this->getMock('AMQPEnvelope');

        $queue = $this->getMock('AMQPQueue');

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

        $consumer->handleDelivery($amqpMessage, $queue);

        $consumer->consume(1);
    }

    public function processMessageProvider()
    {
        return array(
            array(null, 'basic_ack'),
            array(true, 'basic_ack'),
            array(false, 'basic_reject', true),
            array(Consumer::MSG_ACK, 'basic_ack'),
            array(Consumer::MSG_REJECT_REQUEUE, 'basic_reject', true),
            array(Consumer::MSG_REJECT, 'basic_reject', false),
        );
    }
}
