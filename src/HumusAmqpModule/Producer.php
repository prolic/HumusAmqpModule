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

namespace HumusAmqpModule;

use AMQPExchange;

class Producer implements ProducerInterface
{
    /**
     * @var AMQPExchange
     */
    protected $exchange;

    /**
     * Constructor
     *
     * @param AMQPExchange $exchange
     */
    public function __construct(AMQPExchange $exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * @param string $body
     * @param string $routingKey
     * @param array|\Traversable|MessageAttributes|null $attributes
     */
    public function publish($body, $routingKey = '', $attributes = null)
    {
        if (!$attributes instanceof MessageAttributes) {
            $attributes = new MessageAttributes($attributes);
        }

        $this->exchange->publish($body, $routingKey, $attributes->getFlags(), $attributes->toArray());
    }

    /**
     * @param array $bodies
     * @param string $routingKey
     * @param array|\Traversable|MessageAttributes|null $attributes
     */
    public function publishBatch(array $bodies, $routingKey = '', $attributes = null)
    {
        if (!$attributes instanceof MessageAttributes) {
            $attributes = new MessageAttributes($attributes);
        }

        $flags = $attributes->getFlags();
        $attributes = $attributes->toArray();

        foreach ($bodies as $body) {
            $this->exchange->publish($body, $routingKey, $flags, $attributes);
        }
    }
}
