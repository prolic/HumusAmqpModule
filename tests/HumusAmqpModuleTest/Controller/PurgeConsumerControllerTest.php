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

use HumusAmqpModuleTest\Controller\TestAsset\ConsoleAdapter;
use Zend\ServiceManager\ServiceManager;
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

    public function testDispatchWithTestConsumer()
    {
        $consumer = $this->getMockBuilder(__NAMESPACE__ . '\TestAsset\TestConsumer')
            ->setMethods(['purge'])
            ->getMock();
        $consumer
            ->expects($this->once())
            ->method('purge');

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('HumusAmqpModule\PluginManager\Consumer', $cm = new ServiceManager());
        $cm->setService('test-consumer', $consumer);

        ob_start();
        $this->dispatch('humus amqp purge-consumer test-consumer --no-confirmation');

        $this->assertResponseStatusCode(0);
        $res = ob_get_clean();

        $this->assertNotFalse(strstr($res, 'OK'));
    }

    public function testDispatchWithInvalidConsumerName()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('HumusAmqpModule\PluginManager\Consumer', $cm = new ServiceManager());

        ob_start();
        $this->dispatch('humus amqp purge-consumer invalid-consumer --no-confirmation');

        $this->assertResponseStatusCode(1);
        $res = ob_get_clean();

        $this->assertNotFalse(strstr($res, 'ERROR: Consumer "invalid-consumer" not found'));
    }

    public function testPromptNoResponse()
    {
        $consumer = $this->getMockBuilder(__NAMESPACE__ . '\TestAsset\TestConsumer')->getMock();

        $adapter = new ConsoleAdapter();
        $adapter->stream = fopen('php://memory', 'w+');

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('HumusAmqpModule\PluginManager\Consumer', $cm = new ServiceManager());
        $serviceManager->setService('Console', $adapter);
        $cm->setService('test-consumer', $consumer);

        fwrite($adapter->stream, 'n');

        ob_start();

        $this->dispatch('humus amqp purge-consumer test-consumer');

        $this->assertResponseStatusCode(0);
        $res = ob_get_clean();

        $this->assertNotFalse(strstr($res, 'Purging cancelled!'));
    }
}
