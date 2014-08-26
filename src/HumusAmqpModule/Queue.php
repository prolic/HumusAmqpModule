<?php

namespace HumusAmqp\Builder;

use AMQPChannel;
use AMQPQueue;
use HumusAmqp\QueueSpecification;

class Queue
{
    /**
     * @param QueueSpecification $specification
     * @param AMQPChannel $channel
     * @return AMQPQueue
     */
    public function create(QueueSpecification $specification, AMQPChannel $channel)
    {
        $channel->setPrefetchCount($specification->getQosOptions()->getPrefetchCount());
        $channel->setPrefetchSize($specification->getQosOptions()->getPrefetchSize());

        $queue = new AMQPQueue($channel);
        $queue->setName($specification->getName());
        $queue->setFlags($specification->getFlags());
        $queue->setArguments($specification->getArguments());

        if ($specification->getAutoDeclare()) {
            $queue->declareQueue();
            foreach ($specification->getRoutingKeys() as $routingKey) {
                $queue->bind($specification->getExchangeName(), $routingKey, $specification->getBindArguments());
            }
        }

        return $queue;
    }
}
