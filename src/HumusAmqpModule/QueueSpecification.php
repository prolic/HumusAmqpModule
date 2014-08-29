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

use Zend\Stdlib\AbstractOptions;

class QueueSpecification extends AbstractOptions
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $callback;

    /**
     * @var string
     */
    protected $exchange = '';

    /**
     * @var bool
     */
    protected $passive = false;

    /**
     * @var bool
     */
    protected $durable = true;

    /**
     * @var bool
     */
    protected $exclusive = false;

    /**
     * @var bool
     */
    protected $autoDelete = false;

    /**
     * @var array
     */
    protected $arguments = array();

    /**
     * @var array
     */
    protected $routingKeys = array();

    /**
     * @var array
     */
    protected $bindArguments = array();

    /**
     * @return int
     */
    public function getFlags()
    {
        $flags = 0;
        $flags |= $this->getPassive() ? AMQP_PASSIVE : 0;
        $flags |= $this->getDurable() ? AMQP_DURABLE : 0;
        $flags |= $this->getExclusive() ? AMQP_EXCLUSIVE : 0;
        $flags |= $this->getAutoDelete() ? AMQP_AUTODELETE : 0;

        return $flags;
    }

    /**
     * @param string $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param string $exchangeName
     */
    public function setExchange($exchangeName)
    {
        $this->exchange = $exchangeName;
    }

    /**
     * @return string
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param boolean $autoDelete
     */
    public function setAutoDelete($autoDelete)
    {
        $this->autoDelete = $autoDelete;
    }

    /**
     * @return boolean
     */
    public function getAutoDelete()
    {
        return $this->autoDelete;
    }

    /**
     * @param boolean $durable
     */
    public function setDurable($durable)
    {
        $this->durable = $durable;
    }

    /**
     * @return boolean
     */
    public function getDurable()
    {
        return $this->durable;
    }

    /**
     * @param boolean $exclusive
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;
    }

    /**
     * @return boolean
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

    /**
     * @param boolean $passive
     */
    public function setPassive($passive)
    {
        $this->passive = $passive;
    }

    /**
     * @return boolean
     */
    public function getPassive()
    {
        return $this->passive;
    }

    /**
     * @param array $routingKeys
     */
    public function setRoutingKeys(array $routingKeys)
    {
        $this->routingKeys = $routingKeys;
    }

    /**
     * @return array
     */
    public function getRoutingKeys()
    {
        return $this->routingKeys;
    }

    /**
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $bindArguments
     */
    public function setBindArguments(array $bindArguments)
    {
        $this->bindArguments = $bindArguments;
    }

    /**
     * @return array
     */
    public function getBindArguments()
    {
        return $this->bindArguments;
    }

    /**
     * @param string|null $value
     * @throws Exception\InvalidArgumentException
     */
    public function setMatchHeadersExchange($value = null)
    {
        if (null === $value) {
            unset($this->bindArguments['x-match']);
        } else {

            if (!in_array($value, array('all', 'any'))) {
                throw new Exception\InvalidArgumentException(
                    'x-match attribute expected value "all" or "any", "' . $value . '" given'
                );
            }

            $this->bindArguments['x-match'] = $value;
        }
    }

    /**
     * @return string|false
     */
    public function getMatchHeadersExchange()
    {
        return isset($this->bindArguments['x-match']) ? $this->bindArguments['x-match'] : false;
    }

    /**
     * Set HA policy (see: https://www.rabbitmq.com/ha.html)
     *
     * @param string|null $policy
     */
    public function setHaPolicy($policy = null)
    {
        if (null === $policy) {
            unset($this->arguments['x-ha-policy']);
        } else {
            $this->arguments['x-ha-policy'] = $policy;
        }
    }

    /**
     * Get HA policy (see: https://www.rabbitmq.com/ha.html)
     *
     * @return string|false
     */
    public function getHaPolicy()
    {
        return isset($this->arguments['x-ha-policy']) ? $this->arguments['x-ha-policy'] : false;
    }

    /**
     * Set expires (RabbitMQ Extension)
     *
     * @param int|null $expires
     */
    public function setExpires($expires = null)
    {
        if (null === $expires) {
            unset($this->arguments['x-expires']);
        } else {
            $this->arguments['x-expires'] = (int) $expires;
        }
    }

    /**
     * Get expires (RabbitMQ Extension)
     *
     * @return int|false
     */
    public function getExpires()
    {
        return isset($this->arguments['x-expires']) ? $this->arguments['x-expires'] : false;
    }

    /**
     * Set message TTL (RabbitMQ Extension)
     *
     * @param int|null $ttl
     */
    public function setMessageTtl($ttl = null)
    {
        if (null === $ttl) {
            unset($this->arguments['x-message-ttl']);
        } else {
            $this->arguments['x-message-ttl'] = (int) $ttl;
        }
    }

    /**
     * Get message TTL (RabbitMQ Extension)
     *
     * @return int|false
     */
    public function getMessageTtl()
    {
        return isset($this->arguments['x-message-ttl']) ? $this->arguments['x-message-ttl'] : false;
    }

    /**
     * Set dead letter exchange (RabbitMQ Extension)
     *
     * @param string|null $deadLetterExchange
     */
    public function setDeadLetterExchange($deadLetterExchange = null)
    {
        if (null === $deadLetterExchange) {
            unset($this->arguments['x-dead-letter-exchange']);
        } else {
            $this->arguments['x-dead-letter-exchange'] = $deadLetterExchange;
        }
    }

    /**
     * Get dead letter exchange (RabbitMQ Extension)
     *
     * @return string|false
     */
    public function getDeadLetterExchange()
    {
        return isset($this->arguments['x-dead-letter-exchange']) ? $this->arguments['x-dead-letter-exchange'] : false;
    }

    /**
     * Set dead letter routing key (RabbitMQ Extension)
     *
     * @param string|null $routingKey
     */
    public function setDeadLetterRoutingKey($routingKey = null)
    {
        if (null === $routingKey) {
            unset($this->arguments['x-dead-letter-routing-key']);
        } else {
            $this->arguments['x-dead-letter-routing-key'] = $routingKey;
        }
    }

    /**
     * Get dead letter routing key (RabbitMQ Extension)
     *
     * @return string|false
     */
    public function getDeadLetterRoutingKey()
    {
        return isset($this->arguments['x-dead-letter-routing-key'])
            ? $this->arguments['x-dead-letter-routing-key']
            : false;
    }

    /**
     * Set max length (RabbitMQ Extension)
     *
     * @param int|null $maxLength
     */
    public function setMaxLength($maxLength = null)
    {
        if (null === $maxLength) {
            unset($this->arguments['x-max-length']);
        } else {
            $this->arguments['x-max-length'] = (int) $maxLength;
        }
    }

    /**
     * Get max length (RabbitMQ Extension)
     *
     * @return int|false
     */
    public function getMaxLength()
    {
        return isset($this->arguments['x-max-length']) ? $this->arguments['x-max-length'] : false;
    }
}
