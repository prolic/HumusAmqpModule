<?php

namespace HumusAmqpModule\Service;

class AnonConsumerAbstractServiceFactory extends ConsumerAbstractServiceFactory
{
    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'anon_consumers';
}
