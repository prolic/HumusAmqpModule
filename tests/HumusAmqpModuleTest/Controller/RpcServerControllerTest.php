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

class RpcServerControllerTest extends AbstractConsoleControllerTestCase
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
        $rpcServer = $this->getMock(__NAMESPACE__ . '\TestAsset\TestRpcServer', array('start'));
        $rpcServer
            ->expects($this->once())
            ->method('start')
            ->with(100);

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $manager = $serviceManager->get('HumusAmqpModule\PluginManager\RpcServer');
        $manager->setService('test-rpc-server', $rpcServer);

        $this->dispatch('humus amqp rpc-server test-rpc-server 100');
        $this->assertResponseStatusCode(0);
    }

    public function testDispatchWithInvalidAmount()
    {
        $rpcServer = $this->getMock(__NAMESPACE__ . '\TestAsset\TestRpcServer', array('start'));

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('test-rpc-server', $rpcServer);

        ob_start();
        $this->dispatch('humus amqp rpc-server test-rpc-server invalidamount');
        $res = ob_get_clean();
        $this->assertResponseStatusCode(0);

        $this->assertNotFalse($res, 'Error: amount should be null or greater than 0');
    }

    public function testDispatchWithInvalidRpcServer()
    {
        ob_start();
        $this->dispatch('humus amqp rpc-server test-rpc-server');
        $res = ob_get_clean();
        $this->assertResponseStatusCode(0);

        $this->assertNotFalse($res, 'ERROR: RPC-Server "test-rpc-server" not found');
    }
}
