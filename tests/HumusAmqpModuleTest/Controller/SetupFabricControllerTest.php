<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

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
/*
    public function testDispatch()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $c = $serviceManager->get('Config');
        $c['humus_amqp_module']['exchanges'] = array(
            'foo' => array(
                'name' => 'foo',
                'type' => 'direct'
            )
        );
        $c['humus_amqp_module']['queues'] = array(
            'bar' => array(
                'name' => 'bar',
                'type' => 'direct',
                'exchange' => 'foo'
            )
        );
        $serviceManager->setService('Config', $c);
        $cm = $serviceManager->get('ControllerManager');
        $controller = new TestAsset\SetupFabricController();
        $controller->setConsole(new TestAsset\ConsoleAdapter());
        $controller->setConfig($c['humus_amqp_module']);
        $cm->setService('HumusAmqpModule\Controller\SetupFabric', $controller);
        $c = $cm->get('HumusAmqpModule\Controller\SetupFabric');

        ob_start();
        $this->dispatch('humus amqp setup-fabric');
        $res = ob_get_clean();

        $this->assertResponseStatusCode(1);
        $this->assertNotFalse(strstr($res, 'No queues found to configure'));
    }*/

    public function testDispatchWithEmptyExchangeConfig()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Config', array(
            'humus_amqp_module' => array(
                'exchanges' => array(),
                'queues' => array()
            )
        ));

        ob_start();
        $this->dispatch('humus amqp setup-fabric');
        $this->assertResponseStatusCode(1);
        $res = ob_get_clean();

        $this->assertNotFalse(strstr($res, 'No exchanges found to configure'));
    }

    public function testDispatchWithEmptyQueueConfig()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Config', array(
            'humus_amqp_module' => array(
                'exchanges' => array(
                    'foo' => array(
                        'name' => 'foo',
                        'type' => 'direct'
                    )
                ),
                'queues' => array()
            )
        ));

        ob_start();
        $this->dispatch('humus amqp setup-fabric');
        $this->assertResponseStatusCode(1);
        $res = ob_get_clean();

        $this->assertNotFalse(strstr($res, 'No queues found to configure'));
    }
}
