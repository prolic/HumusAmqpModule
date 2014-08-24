<?php

namespace HumusAmqp;

interface ProducerInterface
{
    /**
     * @param string $body
     * @param string $routingKey
     * @param array|\Traversable|MessageAttributes $attributes
     */
    public function publish($body, $routingKey, $attributes);

    /**
     * @param array $bodies
     * @param string $routingKey
     * @param array|\Traversable|MessageAttributes $attributes
     */
    public function publishBatch(array $bodies, $routingKey, $attributes);
}
