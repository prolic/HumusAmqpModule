<?php

namespace HumusAmqpModuleTest\Controller;

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
        $anonConsumer = $this->getMock('HumusAmqp\Amqp\AnonConsumer', array('setupFabric'));
        $anonConsumer
            ->expects($this->once())
            ->method('setupFabric');

        $partsHolder = $this->getMock('HumusAmqpModule\Amqp\PartsHolder');
        $partsHolder
            ->expects($this->any())
            ->method('hasParts')
            ->with($this->anything())
            ->willReturnOnConsecutiveCalls(false, false, true,false, false);

        $partsHolder
            ->expects($this->once())
            ->method('getParts')
            ->with($this->anything())
            ->willReturn(array('foo' => $anonConsumer));


        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('HumusAmqpModule\Amqp\PartsHolder', $partsHolder);

        ob_start();
        $this->dispatch('humus amqp setup-fabric');
        $this->assertResponseStatusCode(0);
        $res = ob_get_clean();

        $this->assertNotFalse(strstr($res, 'No consumers found to configure'));
        $this->assertNotFalse(strstr($res, 'No multiple_consumers found to configure'));
        $this->assertNotFalse(strstr($res, 'Declaring exchanges and queues for anon_consumers'));
        $this->assertNotFalse(strstr($res, 'No rpc_servers found to configure'));
        $this->assertNotFalse(strstr($res, 'No producers found to configure'));
    }
}
