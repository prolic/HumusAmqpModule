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
*/
}
