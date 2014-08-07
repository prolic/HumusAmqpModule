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

namespace HumusAmqpModuleTest\Service;

use HumusAmqpModuleTest\ServiceManagerTestCase;
use Zend\ServiceManager\ServiceManager;

class PartsHolderFactoryTest extends ServiceManagerTestCase
{
    public function testCreateService()
    {
        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $config = $serviceManager->get('Config');
        $config['humus_amqp_module']['producers'] = array(
            'test-producer' => array(
                'connection' => 'default'
            )
        );
        $config['humus_amqp_module']['connections'] = array(
            'default' => array(
                'host' => 'localhost',
                'port' => 5672,
                'user' => 'guest',
                'password' => 'guest',
                'vhost' => '/',
                'lazy' => true
            )
        );
        $serviceManager->setService('Config', $config);

        $namespaces = array(
            'Callback' => 'callbacks',
            'Connection' => 'connections',
            'Producer' => 'producers',
            'Consumer' => 'consumers',
            'MultipleConsumer' => 'mutiple_consumers',
            'AnonConsumer' => 'anon_consumers',
            'RpcClient' => 'rpc_clients',
            'RpcServer' => 'rpc_servers'
        );

        // register plugin managers
        foreach ($namespaces as $ns => $configKey) {
            $serviceName = 'HumusAmqpModule\\PluginManager\\' . $ns;
            $serviceConfig = isset($config['humus_amqp_module']['plugin_managers'][$configKey])
                ? $config['humus_amqp_module']['plugin_managers'][$configKey]
                : array();

            $service = new $serviceName(new \Zend\ServiceManager\Config($serviceConfig));
            // add abstract factory
            if ('Callback' != $ns) { // callbacks are defined in plugin manager configuration
                $service->addAbstractFactory('HumusAmqpModule\\Service\\' . $ns . 'AbstractServiceFactory');
            }
            $service->setServiceLocator($serviceManager);
            $serviceManager->setService($serviceName, $service);
        }

        $partsHolder = $serviceManager->get('HumusAmqpModule\Amqp\PartsHolder');

        $this->assertInstanceOf('HumusAmqpModule\Amqp\PartsHolder', $partsHolder);
        $this->assertTrue($partsHolder->hasParts('producers'));
        $this->assertFalse($partsHolder->hasParts('invalid stuff'));
        $parts = $partsHolder->getParts('producers');
        $this->assertInternalType('array', $parts);
        $this->assertCount(1, $parts);
    }
}
