<?php

namespace HumusAmqpModuleTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class PurgeConsumerControllerTest extends AbstractConsoleControllerTestCase
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
        $consumer = $this->getMock(__NAMESPACE__ . '\TestAsset\TestConsumer', array('purge'));
        $consumer
            ->expects($this->once())
            ->method('purge');

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('test-consumer', $consumer);

        ob_start();
        $this->dispatch('humus amqp purge test-consumer --no-confirmation');

        $this->assertResponseStatusCode(0);
        $res = ob_get_clean();

        $this->assertNotFalse(strstr($res, 'OK'));
    }

    public function testDispatchWithInvalidConsumerName()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        ob_start();
        $this->dispatch('humus amqp purge invalid-consumer --no-confirmation');

        $this->assertResponseStatusCode(0);
        $res = ob_get_clean();

        $this->assertNotFalse(strstr($res, 'ERROR: Consumer "invalid-consumer" not found'));
    }
}
