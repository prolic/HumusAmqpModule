<?php

namespace HumusAmqpModuleTest\Controller\TestAsset;

class SetupFabricController extends \HumusAmqpModule\Controller\SetupFabricController
{
    protected function createChannel(array $spec, $defaultConnectionName)
    {
        $gen = new \PHPUnit_Framework_MockObject_Generator();
        $mock = $gen->getMock('AMQPChannel', [], [], '', false);

        return $mock;
    }
}
