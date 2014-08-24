<?php

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
