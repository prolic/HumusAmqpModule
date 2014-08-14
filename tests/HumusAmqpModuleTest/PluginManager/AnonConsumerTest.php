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

namespace HumusAmqpModuleTest\PluginManager;

use HumusAmqpModule\PluginManager\AnonConsumer as AnonConsumerPluginManager;

class AnonConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatePlugin()
    {
        $options = $this->getMock('HumusAmqpModule\\Amqp\\QueueOptions');
        $options
            ->expects($this->once())
            ->method('getName')
            ->willReturn(null);

        $mock = $this->getMockForAbstractClass('HumusAmqpModule\\Amqp\\ConsumerInterface');
        $mock
            ->expects($this->once())
            ->method('getQueueOptions')
            ->willReturn($options);

        $manager = new AnonConsumerPluginManager();
        $manager->validatePlugin($mock);
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     */
    public function testInvalidPlugin()
    {
        $manager = new AnonConsumerPluginManager();
        $manager->validatePlugin('foo');
    }
}
