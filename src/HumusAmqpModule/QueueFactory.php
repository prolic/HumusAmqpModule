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
        $channel->setPrefetchCount($specification->getQosOptions()->getPrefetchCount());
        $channel->setPrefetchSize($specification->getQosOptions()->getPrefetchSize());

        $queue = new AMQPQueue($channel);
        $queue->setName($specification->getName());
        $queue->setFlags($specification->getFlags());
        $queue->setArguments($specification->getArguments());

        if ($autoDeclare) {
            // @todo: declare exchanges, first
            $queue->declareQueue();
            foreach ($specification->getRoutingKeys() as $routingKey) {
                $queue->bind($specification->getExchangeName(), $routingKey, $specification->getBindArguments());
            }
        }

        return $queue;
    }
}
