<?php

namespace HumusAmqpModuleTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class ConsumerControllerTest extends AbstractConsoleControllerTestCase
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
        $consumer = $this->getMock(__NAMESPACE__ . '\TestAsset\TestConsumer', array('consume'));
        $consumer
            ->expects($this->once())
            ->method('consume')
            ->with(5);

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('test-consumer', $consumer);

        ob_start();
        $this->dispatch('humus amqp consumer test-consumer 5 --route=bar --memory_limit=1G --without-signals');
        ob_clean();

        $this->assertResponseStatusCode(0);
    }

    public function testDispatchWithInvalidConsumerName()
    {
        ob_start();
        $this->dispatch('humus amqp consumer invalid-consumer');
        $res = ob_get_clean();

        $this->assertResponseStatusCode(0);
        $this->assertNotFalse($res, strstr($res, 'Error: unknown consumer "invalid-consumer"'));
    }

    public function testDispatchWithWrongServiceName()
    {
        ob_start();
        $this->dispatch('humus amqp consumer EventManager');
        $res = ob_get_clean();

        $this->assertResponseStatusCode(0);
        $this->assertNotFalse($res, strstr($res, 'Error: unknown consumer "invalid-consumer"'));
    }

    public function testDispatchWithInvalidAmount()
    {
        $consumer = $this->getMock(__NAMESPACE__ . '\TestAsset\TestConsumer');

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('test-consumer', $consumer);

        ob_start();
        $this->dispatch('humus amqp consumer test-consumer invalidamount');
        $res = ob_get_clean();

        $this->assertResponseStatusCode(0);
        $this->assertNotFalse($res, strstr($res, 'Error: amount should be null or greater than 0'));
    }
}
