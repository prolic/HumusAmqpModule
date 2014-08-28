<?php

namespace HumusAmqpModule;

use AMQPChannel;
use AMQPExchange;

class ExchangeFactory
{
    /**
     * @param ExchangeSpecification $specification
     * @param AMQPChannel $channel
     * @param bool $autoDeclare
     * @return AMQPExchange
     */
    public function create(ExchangeSpecification $specification, AMQPChannel $channel, $autoDeclare = true)
    {
        $exchange = new AMQPExchange($channel);
        $exchange->setArguments($specification->getArguments());
        $exchange->setName($specification->getName());
        $exchange->setFlags($specification->getFlags());
        $exchange->setType($specification->getType());

        if ($autoDeclare) {
            $exchange->declareExchange();

            // rabbitmq extension: exchange to exchange bindings
            foreach ($specification->getExchangeBindings() as $exchangeName => $routingKeys) {
                foreach ($routingKeys as $routingKey) {
                    $exchange->bind($exchangeName, $routingKey, $specification->getFlags());
                }
            }
        }

        return $exchange;
    }
}
