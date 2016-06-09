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

use Zend\ServiceManager\ServiceManager;
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

    public function testDispatchWithTestConsumer()
    {
        $consumer = $this->getMockBuilder('HumusAmqpModule\Consumer')
            ->setMethods(['consume'])
            ->disableOriginalConstructor()
            ->getMock();
        $consumer
            ->expects($this->once())
            ->method('consume')
            ->with(5);

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('HumusAmqpModule\PluginManager\Consumer', $cm = new ServiceManager());
        $cm->setService('test-consumer', $consumer);

        ob_start();
        $this->dispatch('humus amqp consumer test-consumer 5');
        ob_end_clean();

        $this->assertResponseStatusCode(0);
    }

    public function testDispatchWithInvalidConsumerName()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('HumusAmqpModule\PluginManager\Consumer', $cm = new ServiceManager());

        ob_start();
        $this->dispatch('humus amqp consumer invalid-consumer');
        $res = ob_get_clean();

        $this->assertResponseStatusCode(1);
        $this->assertNotFalse($res, strstr($res, 'Error: unknown consumer "invalid-consumer"'));
    }

    public function testDispatchWithWrongServiceName()
    {
        ob_start();
        $this->dispatch('humus amqp consumer EventManager');
        $res = ob_get_clean();

        $this->assertResponseStatusCode(1);
        $this->assertNotFalse($res, strstr($res, 'Error: unknown consumer "invalid-consumer"'));
    }

    public function testDispatchWithInvalidAmount()
    {
        $consumer = $this->getMockBuilder(__NAMESPACE__ . '\TestAsset\TestConsumer')->getMock();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('HumusAmqpModule\PluginManager\Consumer', $cm = new ServiceManager());
        $cm->setService('test-consumer', $consumer);

        ob_start();
        $this->dispatch('humus amqp consumer test-consumer invalidamount');
        $res = ob_get_clean();

        $this->assertResponseStatusCode(1);
        $this->assertNotFalse($res, strstr($res, 'Error: amount should be null or greater than 0'));
    }
}
