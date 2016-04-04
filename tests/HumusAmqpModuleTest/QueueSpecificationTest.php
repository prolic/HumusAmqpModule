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

namespace HumusAmqpModuleTest;

use HumusAmqpModule\QueueSpecification;

class QueueSpecificationTest extends \PHPUnit_Framework_TestCase
{
    public function testGettersAndSetters()
    {
        $spec = new QueueSpecification([
            'name' => 'name',
            'connection' => 'testconnection',
            'exchange' => 'exchange',
            'passive' => true,
            'durable' => false,
            'exclusive' => true,
            'autoDelete' => true,
            'arguments' => [
                'key1' => 'value1',
                'key2' => 'value2'
            ],
            'routingKeys' => [
                'r1',
                'r2'
            ],
            'bindArguments' => [
                'key3' => 'value3',
                'key4' => 'value4'
            ],
        ]);

        $this->assertSame('name', $spec->getName());
        $this->assertSame('testconnection', $spec->getConnection());
        $this->assertSame('exchange', $spec->getExchange());
        $this->assertTrue($spec->getPassive());
        $this->assertFalse($spec->getDurable());
        $this->assertTrue($spec->getExclusive());
        $this->assertTrue($spec->getAutoDelete());
        $this->assertEquals(
            [
                'key1' => 'value1',
                'key2' => 'value2'
            ],
            $spec->getArguments()
        );
        $this->assertEquals(
            [
                'r1',
                'r2'
            ],
            $spec->getRoutingKeys()
        );
        $this->assertEquals(
            [
                'key3' => 'value3',
                'key4' => 'value4'
            ],
            $spec->getBindArguments()
        );
    }

    public function testRabbitMqExtensions()
    {
        $spec = new QueueSpecification([
            'match_headers_exchange' => 'all',
            'ha_policy' => 'all',
            'ha_policy_params' => [
                'user@hostname',
                'user2@hostname'
            ],
            'expires' => 20,
            'message_ttl' => 10,
            'dead_letter_exchange' => 'bar',
            'dead_letter_routing_key' => 'baz',
            'max_length' => 1024
        ]);

        $this->assertSame('all', $spec->getMatchHeadersExchange());
        $this->assertSame('all', $spec->getHaPolicy());
        $this->assertEquals(
            [
                'user@hostname',
                'user2@hostname'
            ],
            $spec->getHaPolicyParams()
        );
        $this->assertSame(20, $spec->getExpires());
        $this->assertSame(10, $spec->getMessageTtl());
        $this->assertSame('bar', $spec->getDeadLetterExchange());
        $this->assertSame('baz', $spec->getDeadLetterRoutingKey());
        $this->assertSame(1024, $spec->getMaxLength());
        $this->assertEquals(
            [
                'x-match' => 'all'
            ],
            $spec->getBindArguments()
        );
        $this->assertEquals(
            [
                'x-ha-policy' => "all",
                'x-ha-policy-params' => [
                    "user@hostname",
                    "user2@hostname"
                ],
                'x-expires' => 20,
                'x-message-ttl' => 10,
                'x-dead-letter-exchange' => "bar",
                'x-dead-letter-routing-key' => "baz",
                'x-max-length' => 1024
            ],
            $spec->getArguments()
        );

        // reset all
        $spec->setMatchHeadersExchange(null);
        $spec->setHaPolicy(null);
        $spec->setHaPolicyParams(null);
        $spec->setExpires(null);
        $spec->setMessageTtl(null);
        $spec->setDeadLetterExchange(null);
        $spec->setDeadLetterRoutingKey(null);
        $spec->setMaxLength(null);

        $this->assertEmpty($spec->getArguments());
        $this->assertEmpty($spec->getBindArguments());
        $this->assertFalse($spec->getMatchHeadersExchange());
        $this->assertFalse($spec->getHaPolicy());
        $this->assertFalse($spec->getHaPolicyParams());
        $this->assertFalse($spec->getExpires());
        $this->assertFalse($spec->getMessageTtl());
        $this->assertFalse($spec->getDeadLetterExchange());
        $this->assertFalse($spec->getDeadLetterRoutingKey());
        $this->assertFalse($spec->getMaxLength());
    }

    /**
     * @expectedException \HumusAmqpModule\Exception\InvalidArgumentException
     */
    public function testSettingInvalidMatchHeadersExchange()
    {
        $spec = new QueueSpecification();
        $spec->setMatchHeadersExchange('foo');
    }
}
