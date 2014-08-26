<?php

namespace HumusAmqpModule;

use Zend\Stdlib\AbstractOptions;

class ExchangeSpecification extends AbstractOptions
{
    /**
     * @var array
     */
    protected $arguments = array(
        'internal' => false // RabbitMQ Extension
    );

    /**
     * @var bool
     */
    protected $autoDelete = false; // RabbitMQ Extension

    /**
     * @var array
     */
    protected $exchangeBindings = array(); // RabbitMQ Extension

    /**
     * @var bool
     */
    protected $passive = false;

    /**
     * @var bool
     */
    protected $durable = true;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var ExchangeType
     */
    protected $type;

    /**
     * Constructor
     *
     * @param  array|\Traversable|null $options
     */
    public function __construct($options = null)
    {
        $this->type = ExchangeType::get('direct');
        parent::__construct($options);
    }

    /**
     * @return int
     */
    public function getFlags()
    {
        $flags = 0;
        $flags |= $this->getPassive() ? AMQP_PASSIVE : 0;
        $flags |= $this->getDurable() ? AMQP_DURABLE : 0;
        $flags |= $this->getAutoDelete() ? AMQP_AUTODELETE : 0; // RabbitMQ Extension

        return $flags;
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
     * @param ExchangeType|string $type
     * @throws Exception\InvalidArgumentException
     */
    public function setType($type)
    {
        if (is_string($type)) {
            $type = ExchangeType::get($type);
        }
        if (!$type instanceof ExchangeType) {
            throw new Exception\InvalidArgumentException('Invalid type given');
        }
        $this->type = $type;
    }

    /**
     * @return ExchangeType
     */
    public function getType()
    {
        return $this->type;
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
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $args
     */
    public function setArguments(array $args)
    {
        $this->arguments = $args;
    }

    /**
     * Set alternate exchange (RabbitMQ Extension)
     *
     * @param string|null $alternateExchange
     */
    public function setAlternateExchange($alternateExchange = null)
    {
        if (null === $alternateExchange) {
            unset($this->attributes['alternate-exchange']);
        } else {
            $this->attributes['alternate-exchange'] = $alternateExchange;
        }
    }

    /**
     * Get alternate exchange (RabbitMQ Extension)
     *
     * @return string|false
     */
    public function getAlternateExchange()
    {
        return isset($this->attributes['alternate-exchange']) ? $this->attributes['alternate-exchange'] : false;
    }

    /**
     * Set exchange bindings (RabbitMQ Extension)
     *
     * @param array $exchangeBindings
     */
    public function setExchangeBindings(array $exchangeBindings = array())
    {
        $this->exchangeBindings = $exchangeBindings;
    }

    /**
     * Get exchange bindings (RabbitMQ Extension)
     *
     * Example:
     *
     * return array(
     *     'exchange1' => array(
     *         'routingKey.1',
     *         'routingKey.2'
     *     ),
     *     'exchange2' => array(
     *         'routingKey.3'
     *     )
     * );
     *
     * @return array
     */
    public function getExchangeBindings()
    {
        return $this->exchangeBindings;
    }

    /**
     * Set internal flag (RabbitMQ Extension)
     *
     * @param bool $bool
     */
    public function setInternal($bool)
    {
        $this->arguments['internal'] = (bool) $bool;
    }

    /**
     * Get internal flag (RabbitMQ Extension)
     *
     * @return bool
     */
    public function getInternal()
    {
        return $this->arguments['internal'];
    }

    /**
     * Set auto delete flag (RabbitMQ Extension)
     *
     * @param boolean $autoDelete
     */
    public function setAutoDelete($autoDelete)
    {
        $this->autoDelete = $autoDelete;
    }

    /**
     * Get auto delete flag (RabbitMQ Extension)
     *
     * @return boolean
     */
    public function getAutoDelete()
    {
        return $this->autoDelete;
    }
}
