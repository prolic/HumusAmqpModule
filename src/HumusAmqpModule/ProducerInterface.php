<?php

namespace HumusAmqpModule;

interface ProducerInterface
{
    /**
     * @param string $body
     * @param string $routingKey
     * @param array|\Traversable|MessageAttributes|null $attributes
     */
    public function publish($body, $routingKey = '', $attributes = null);

    /**
     * @param array $bodies
     * @param string $routingKey
     * @param array|\Traversable|MessageAttributes|null $attributes
     */
    public function publishBatch(array $bodies, $routingKey = '', $attributes = null);
}
