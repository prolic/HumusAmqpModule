<?php

namespace HumusAmqpModuleTest\Service\TestAsset;

class RpcClientAbstractServiceFactory extends \HumusAmqpModule\Service\RpcClientAbstractServiceFactory
{
    use CreateChannelMockTrait;
}
