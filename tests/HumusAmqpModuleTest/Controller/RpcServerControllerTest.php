<?php

namespace HumusAmqpModuleTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class RpcServerControllerTest extends AbstractConsoleControllerTestCase
{
    protected $useConsoleRequest = true;

    protected $traceError = true;

    protected function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../TestConfiguration.php.dist');
        parent::setUp();
    }

    public function testDispatch()
    {
        $rpcServer = $this->getMock('HumusAmqp\Amqp\RpcServer', array('start'));
        $rpcServer
            ->expects($this->once())
            ->method('start')
            ->with(100);

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('test-rpc-server', $rpcServer);

        $this->dispatch('humus amqp rpc-server test-rpc-server 100');
        $this->assertResponseStatusCode(0);
    }

    public function testDispatchWithInvalidAmount()
    {
        $rpcServer = $this->getMock('HumusAmqp\Amqp\RpcServer', array('start'));

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('test-rpc-server', $rpcServer);

        ob_start();
        $this->dispatch('humus amqp rpc-server test-rpc-server invalidamount');
        $res = ob_get_clean();
        $this->assertResponseStatusCode(0);

        $this->assertNotFalse($res, 'Error: amount should be null or greater than 0');
    }

    public function testDispatchWithInvalidRpcServer()
    {
        ob_start();
        $this->dispatch('humus amqp rpc-server test-rpc-server');
        $res = ob_get_clean();
        $this->assertResponseStatusCode(0);

        $this->assertNotFalse($res, 'ERROR: RPC-Server "test-rpc-server" not found');
    }
}
