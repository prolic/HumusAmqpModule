<?php

namespace HumusAmqpModuleTest\Service\TestAsset;

class ProducerAbstractServiceFactory extends \HumusAmqpModule\Service\ProducerAbstractServiceFactory
{
    protected $mock;

    public function setChannelMock($mock)
    {
        $this->mock = $mock;
    }

    /**
     * @param \AMQPConnection $connection
     * @param array $spec
     * @return \AMQPChannel
     */
    protected function createChannel(\AMQPConnection $connection, array $spec)
    {
        return $this->mock;
    }
}
