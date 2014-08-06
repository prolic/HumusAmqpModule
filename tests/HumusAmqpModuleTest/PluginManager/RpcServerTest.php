<?php

namespace HumusAmqpModuleTest\PluginManager;

use HumusAmqpModule\PluginManager\RpcServer as RpcServerPluginManager;

class RpcServerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidatePlugin()
    {
        $mock = $this->getMock('HumusAmqpModule\\Amqp\\RpcServer', array(), array(), '', false);
        $manager = new RpcServerPluginManager();
        $manager->validatePlugin($mock);
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     */
    public function testInvalidPlugin()
    {
        $manager = new RpcServerPluginManager();
        $manager->validatePlugin('foo');
    }
}
