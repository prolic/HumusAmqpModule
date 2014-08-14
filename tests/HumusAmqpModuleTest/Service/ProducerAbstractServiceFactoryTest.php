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

use HumusAmqpModule\PluginManager\Connection as ConnectionPluginManager;
use HumusAmqpModule\PluginManager\Producer as ProducerPluginManager;
use HumusAmqpModule\Service\ConnectionAbstractServiceFactory;
use HumusAmqpModule\Service\ProducerAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

class ProducerAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var ProducerAbstractServiceFactory
     */
    protected $components;

    public function setUp()
    {
        $config = array(
            'humus_amqp_module' => array(
                'classes' => array(
                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
                    'producer' => 'HumusAmqpModule\Amqp\Producer',
                ),
                'connections' => array(
                    'default' => array(
                        'host' => 'localhost',
                        'port' => 5672,
                        'user' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                        'lazy' => true
                    )
                ),
                'producers' => array(
                    'test-producer' => array(
                        'connection' => 'default',
                        'class' => __NAMESPACE__ . '\\TestAsset\\CustomProducer',
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct',
                            'arguments' => array(),
                            'autoDelete' => false,
                            'declare' => true,
                            'durable' => false,
                            'internal' => true,
                            'nowait' => false,
                            'passive' => true,
                            'ticket' => null
                        ),
                        'queue_options' => array(
                            'passive' => false,
                            'routingKeys' => array(),
                            'arguments' => array(),
                            'autoDelete' => true,
                            'durable' => false,
                            'exclusive' => true,
                            'name' => 'testname',
                            'nowait' => true,
                            'ticket' => null,
                        ),
                        'auto_setup_fabric' => false
                    ),
                    'test-producer-2' => array(
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct'
                        )
                    ),
                )
            )
        );

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $dependentComponent = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $cm = new ConnectionPluginManager());
        $cm->addAbstractFactory($dependentComponent);
        $cm->setServiceLocator($services);

        $components = $this->components = new ProducerAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Producer', $producerManager = new ProducerPluginManager());
        $producerManager->addAbstractFactory($components);
        $producerManager->setServiceLocator($services);
    }

    public function testCreateProducer()
    {
        $producer = $this->components->createServiceWithName($this->services, 'test-producer', 'test-producer');
        $this->assertInstanceOf('HumusAmqpModule\Amqp\Producer', $producer);
        /* @var $producer \HumusAmqpModule\Amqp\Producer */
        $this->assertEquals('demo-exchange', $producer->getExchangeOptions()->getName());
        $this->assertEquals('direct', $producer->getExchangeOptions()->getType());
    }

    public function testCreateProducerWithoutConnectionName()
    {
        $producer = $this->components->createServiceWithName($this->services, 'test-producer-2', 'test-producer-2');
        $this->assertInstanceOf('HumusAmqpModule\Amqp\Producer', $producer);
        /* @var $producer \HumusAmqpModule\Amqp\Producer */
        $this->assertEquals('demo-exchange', $producer->getExchangeOptions()->getName());
        $this->assertEquals('direct', $producer->getExchangeOptions()->getType());
    }

    public function testInvalidConsumerClassResultsCannotCreateInstance()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['producers']['test-producer']['class'] = 'stdClass';
        $this->services->setService('Config', $config);

        $pm = $this->services->get('HumusAmqpModule\PluginManager\Producer');
        try {
            $pm->get('test-producer');
        } catch (\Zend\ServiceManager\Exception\ServiceNotCreatedException $e) {
            // two exceptions backwards
            $p = $e->getPrevious()->getPrevious();
            $this->assertInstanceOf('HumusAmqpModule\Exception\RuntimeException', $p);
            $this->assertEquals(
                'Producer of type stdClass is invalid; must implement HumusAmqpModule\Amqp\Producer',
                $p->getMessage()
            );
        }
    }

    public function testCannotCreateProducerWhenConnectionPluginManagerIsMissing()
    {
        $config = array(
            'humus_amqp_module' => array(
                'classes' => array(
                    'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
                    'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
                    'producer' => 'HumusAmqpModule\Amqp\Producer',
                ),
                'connections' => array(
                    'default' => array(
                        'host' => 'localhost',
                        'port' => 5672,
                        'user' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                        'lazy' => true
                    )
                ),
                'producers' => array(
                    'test-producer' => array(
                        'connection' => 'default',
                        'class' => __NAMESPACE__ . '\\TestAsset\\CustomProducer',
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct'
                        )
                    ),
                    'test-producer-2' => array(
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct'
                        )
                    ),
                )
            )
        );

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $components = $this->components = new ProducerAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Producer', $producerManager = new ProducerPluginManager());
        $producerManager->addAbstractFactory($components);
        $producerManager->setServiceLocator($services);

        try {
            $producerManager->get('test-producer');
        } catch (\Zend\ServiceManager\Exception\ServiceNotCreatedException $e) {
            $p = $e->getPrevious()->getPrevious();
            $this->assertInstanceOf('HumusAmqpModule\Exception\RuntimeException', $p);
            $this->assertEquals('HumusAmqpModule\PluginManager\Connection not found', $p->getMessage());
        }
    }
}
