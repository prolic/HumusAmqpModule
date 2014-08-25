<?php

namespace HumusAmqpModule;

use AMQPChannel;
use AMQPExchange;
use AMQPQueue;

class QueueFactory
{
    /**
     * @param QueueSpecification $specification
     * @param AMQPChannel $channel
     * @param bool $autoDeclare
     * @return AMQPQueue
     */
    public function create(QueueSpecification $specification, AMQPChannel $channel, $autoDeclare = true)
    {
        $queue = new AMQPQueue($channel);
        //$queue->setName($specification->getName());
        $queue->setFlags($specification->getFlags());
        $queue->setArguments($specification->getArguments());

        if ($autoDeclare) {
            // @todo: declare error exchanges, first
            $queue->declareQueue();

            $routingKeys = $specification->getRoutingKeys();
            if (empty($routingKeys)) {
                $queue->bind($specification->getExchange(), null, $specification->getBindArguments());
            } else {
                foreach ($routingKeys as $routingKey) {
                    $queue->bind($specification->getExchange(), $routingKey, $specification->getBindArguments());
                }
            }
        }

        return $queue;
    }
}
