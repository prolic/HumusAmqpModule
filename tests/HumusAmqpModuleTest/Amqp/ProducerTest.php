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
