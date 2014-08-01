<?php

namespace HumusAmqpModuleTest\Controller;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class SetupFabricControllerTest extends AbstractConsoleControllerTestCase
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
        $producer = $this->getMock(__NAMESPACE__ . '\TestAsset\TestProducer', array('setupFabric'));
        $producer
            ->expects($this->once())
            ->method('setupFabric');

        $partsHolder = $this->getMock('HumusAmqpModule\Amqp\PartsHolder', array('hasParts', 'getParts'));
        $partsHolder
            ->expects($this->any())
            ->method('hasParts')
            ->with($this->anything())
            ->willReturnOnConsecutiveCalls(false, false, false, false, true);

        $partsHolder
            ->expects($this->once())
            ->method('getParts')
            ->with($this->anything())
            ->willReturn(array('test-producer' => $producer));

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('HumusAmqpModule\Amqp\PartsHolder', $partsHolder);

        ob_start();
        $this->dispatch('humus amqp setup-fabric');
        $this->assertResponseStatusCode(0);
        $res = ob_get_clean();

        $this->assertNotFalse(strstr($res, 'No consumers found to configure'));
        $this->assertNotFalse(strstr($res, 'No multiple_consumers found to configure'));
        $this->assertNotFalse(strstr($res, 'No anon_consumers found to configure'));
        $this->assertNotFalse(strstr($res, 'No rpc_servers found to configure'));
        $this->assertNotFalse(strstr($res, 'Declaring exchanges and queues for producers'));
    }
}
