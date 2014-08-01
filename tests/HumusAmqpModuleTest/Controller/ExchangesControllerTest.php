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
