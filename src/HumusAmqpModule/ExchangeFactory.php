<?php

namespace HumusAmqpModule;

use AMQPChannel;
use AMQPExchange;

class ExchangeFactory
{
    /**
     * @param ExchangeSpecification $specification
     * @param AMQPChannel $channel
     * @return AMQPExchange
     */
    public function create(ExchangeSpecification $specification, AMQPChannel $channel)
    {
        $exchange = new AMQPExchange($channel);
        $exchange->setType($specification->getType());
        $exchange->setFlags($specification->getFlags());
        $exchange->setArguments($specification->getArguments());

        if ($specification->getAutoDeclare()) {
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
