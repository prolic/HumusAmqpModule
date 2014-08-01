<?php

namespace HumusAmqpModuleTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class StdInProducerControllerTest extends AbstractConsoleControllerTestCase
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
        $producer = $this->getMock(__NAMESPACE__ . '\TestAsset\Producer', array('publish'));
        $producer
            ->expects($this->once())
            ->method('publish')
            ->with('foo', 'bar');

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('test-producer', $producer);

        ob_start();
        $this->dispatch('humus amqp stdin-producer test-producer --route=bar foo');
        ob_clean();

        $this->assertResponseStatusCode(0);
    }

    public function testDispatchWithInvalidProducerName()
    {
        ob_start();
        $this->dispatch('humus amqp stdin-producer test-producer --route=bar foo');
        $res = ob_get_clean();

        $this->assertResponseStatusCode(0);
        $this->assertNotFalse(strstr($res, 'ERROR: Producer "test-producer" not found'));
    }
}
