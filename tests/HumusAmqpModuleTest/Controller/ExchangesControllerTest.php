<?php

namespace HumusAmqpModuleTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class ExchangesControllerTest extends AbstractConsoleControllerTestCase
{
    protected $useConsoleRequest = true;

    protected $traceError = true;

    protected function setUp()
    {
        $this->setApplicationConfig(include __DIR__ . '/../../TestConfiguration.php.dist');
        parent::setUp();
    }

    public function testDispatchWithEmptyConfig()
    {
        ob_start();
        $this->dispatch('humus amqp list-exchanges');
        $res = ob_get_clean();

        $this->assertResponseStatusCode(0);
        $this->assertNotFalse(strstr($res, 'List of all exchanges'));
    }

    public function testDispatchWithConfig()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $config = $serviceManager->get('Config');
        $config['humus_amqp_module']['consumers']['myconsumer'] = array(
            'exchange_options' => array(
                'name' => 'foo',
                'type' => 'topic'
            )
        );
        $config['humus_amqp_module']['consumers']['myconsumer-2'] = array(
            'exchange_options' => array(
                'name' => 'bar',
                'type' => 'topic'
            )
        );
        $config['humus_amqp_module']['rpc_servers']['rpc'] = array(
            'exchange_options' => array(
                'name' => 'baz',
                'type' => 'direct'
            )
        );
        $serviceManager->setService('Config', $config);

        ob_start();
        $this->dispatch('humus amqp list-exchanges');
        $res = ob_get_clean();

        $this->assertResponseStatusCode(0);

        $this->assertRegExp(
            '/.+List of all exchanges.+\n.+Exchange-Type: topic.+\nfoo\nbar\n.+Exchange-Type: direct.+\nbaz/',
            $res
        );
    }
}
