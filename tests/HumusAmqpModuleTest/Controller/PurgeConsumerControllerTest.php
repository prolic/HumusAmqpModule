<?php

namespace HumusAmqpModuleTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class PurgeConsumerControllerTest extends AbstractConsoleControllerTestCase
{
    protected $useConsoleRequest = true;

    protected $traceError = true;

    protected function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../../TestConfiguration.php.dist');
        parent::setUp();
    }

    public function testDispatch()
    {
        $consumer = $this->getMock('HumusAmqp\Amqp\Consumer', array('purge'));
        $consumer
            ->expects($this->once())
            ->method('purge');

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('test-consumer', $consumer);

        ob_start();
        $this->dispatch('humus amqp purge test-consumer');
        $this->assertResponseStatusCode(0);
        $res = ob_get_clean();

        $this->assertNotFalse(strstr($res, 'No consumers found to configure'));
        $this->assertNotFalse(strstr($res, 'No multiple_consumers found to configure'));
        $this->assertNotFalse(strstr($res, 'Declaring exchanges and queues for anon_consumers'));
        $this->assertNotFalse(strstr($res, 'No rpc_servers found to configure'));
        $this->assertNotFalse(strstr($res, 'No producers found to configure'));
    }
}
