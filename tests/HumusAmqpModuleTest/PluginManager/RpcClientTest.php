<?php

namespace HumusAmqpModuleTest\PluginManager;

use HumusAmqpModule\PluginManager\RpcClient as RpcClientPluginManager;

class RpcClientTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatePlugin()
    {
        $mock = $this->getMock('HumusAmqpModule\\Amqp\\RpcClient', array(), array(), '', false);
        $manager = new RpcClientPluginManager();
        $manager->validatePlugin($mock);
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     */
    public function testInvalidPlugin()
    {
        $manager = new RpcClientPluginManager();
        $manager->validatePlugin('foo');
    }
}
