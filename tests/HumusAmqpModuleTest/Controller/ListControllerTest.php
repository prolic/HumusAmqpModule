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

        $config['humus_amqp_module']['consumers'] = [
            'testconsumer-1' => [],
            'testconsumer-2' => []
        ];
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

        $config['humus_amqp_module'] = [];
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

        $config['humus_amqp_module']['rpc_servers'] = [];
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
