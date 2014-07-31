<?php

namespace HumusAmqpModuleTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class ListControllerTest extends AbstractConsoleControllerTestCase
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
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $config = $serviceManager->get('Config');

        $config['humus_amqp_module']['consumers'] = array(
            'testconsumer-1' => array(),
            'testconsumer-2' => array()
        );
        $serviceManager->setService('Config', $config);


        ob_start();
        $this->dispatch('humus amqp list consumers');

        $this->assertResponseStatusCode(0);
        $res = ob_get_clean();

        $this->assertRegExp(
            '/.+List of all available consumers.+\ntestconsumer-1\ntestconsumer-2/',
            $res
        );
    }

    public function testDispatchWithoutConsumers()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $config = $serviceManager->get('Config');

        $config['humus_amqp_module'] = array();
        $serviceManager->setService('Config', $config);


        ob_start();
        $this->dispatch('humus amqp list consumers');
        $res = ob_get_clean();

        $this->assertRegExp(
            '/.+List of all available consumers.+\n.+No consumers found.+/',
            $res
        );
    }

    public function testDispatchWithoutRpcServersInStack()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $config = $serviceManager->get('Config');

        $config['humus_amqp_module']['rpc_servers'] = array();
        $serviceManager->setService('Config', $config);


        ob_start();
        $this->dispatch('humus amqp list rpc-servers');
        $res = ob_get_clean();

        $this->assertRegExp(
            '/.+List of all available rpc-servers.+\n.+No rpc-servers found.+/',
            $res
        );
    }
}
