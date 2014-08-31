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
use Zend\ServiceManager\ServiceManager;

class ProducerAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var TestAsset\ProducerAbstractServiceFactory
     */
    protected $components;

    public function setUp()
    {
        $config = array(
            'humus_amqp_module' => array(
                'default_connection' => 'default',
                'connections' => array(
                    'default' => array(
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                    )
                ),
                'exchanges' => array(
                    'demo-exchange' => array(
                        'name' => 'demo-exchange',
                        'type' => 'direct',
                        'durable' => false,
                        'autoDelete' => true
                    )
                ),
                'queues' => array(
                    'test-queue' => array(
                        'name' => 'test-queue',
                        'exchange' => 'demo-exchange',
                        'autoDelete' => true
                    )
                ),
                'producers' => array(
                    'test-producer' => array(
                        'connection' => 'default',
                        'exchange' => 'demo-exchange',
                        'auto_setup_fabric' => true
                    ),
                    'test-producer-2' => array(
                        'exchange' => 'demo-exchange',
                        'auto_setup_fabric' => true
                    ),
                    'test-producer-3' => array(
                    ),
                    'test-producer-4' => array(
                        'exchange' => 'missing-exchange'
                    )
                )
            )
        );

        $connection = $this->getMock('AMQPConnection', array(), array(), '', false);
        $channel    = $this->getMock('AMQPChannel', array(), array(), '', false);
        $channel
            ->expects($this->any())
            ->method('getPrefetchCount')
            ->will($this->returnValue(10));
        $exchange      = $this->getMock('AMQPExchange', array(), array(), '', false);
        $exchangeFactory = $this->getMock('HumusAmqpModule\ExchangeFactory');
        $exchangeFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($exchange));

        $connectionManager = $this->getMock('HumusAmqpModule\PluginManager\Connection');
        $connectionManager
            ->expects($this->any())
            ->method('get')
            ->with('default')
            ->willReturn($connection);

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $dependentComponent = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $cm = new ConnectionPluginManager());
        $cm->addAbstractFactory($dependentComponent);
        $cm->setServiceLocator($services);

        $components = $this->components = new TestAsset\ProducerAbstractServiceFactory();
        $components->setChannelMock($channel);
        $components->setExchangeFactory($exchangeFactory);
        $services->setService('HumusAmqpModule\PluginManager\Producer', $producerManager = new ProducerPluginManager());
        $producerManager->addAbstractFactory($components);
        $producerManager->setServiceLocator($services);
    }

    public function testCreateProducer()
    {
        $producer = $this->components->createServiceWithName($this->services, 'test-producer', 'test-producer');
        $this->assertInstanceOf('HumusAmqpModule\ProducerInterface', $producer);
    }

    public function testCreateProducerWithoutConnectionName()
    {
        $producer = $this->components->createServiceWithName($this->services, 'test-producer-2', 'test-producer-2');
        $this->assertInstanceOf('HumusAmqpModule\ProducerInterface', $producer);
    }

    public function testCreateProducerWithoutExchangeThrowsException()
    {
        $this->setExpectedException(
            'HumusAmqpModule\Exception\InvalidArgumentException',
            'Exchange is missing for producer test-producer-3'
        );
        $this->components->createServiceWithName($this->services, 'test-producer-3', 'test-producer-3');
    }

    public function testCreateProducerWithoutExchangeConfigThrowsException()
    {
        $this->setExpectedException(
            'HumusAmqpModule\Exception\InvalidArgumentException',
            'The producer exchange missing-exchange is missing in the exchanges configuration'
        );
        $this->components->createServiceWithName($this->services, 'test-producer-4', 'test-producer-4');
    }

    public function testCannotCreateProducerWhenConnectionPluginManagerIsMissing()
    {
        $config = array(
            'humus_amqp_module' => array(
                'default_connection' => 'default',
                'connections' => array(
                    'default' => array(
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                    )
                ),
                'exchanges' => array(
                    'demo-exchange' => array(
                        'name' => 'demo-exchange',
                        'type' => 'direct',
                        'durable' => false,
                        'autoDelete' => true
                    )
                ),
                'queues' => array(
                    'test-queue' => array(
                        'name' => 'test-queue',
                        'exchange' => 'demo-exchange',
                        'autoDelete' => true
                    )
                ),
                'producers' => array(
                    'test-producer' => array(
                        'connection' => 'default',
                        'exchange' => 'demo-exchange',
                        'auto_setup_fabric' => true
                    ),
                    'test-producer-2' => array(
                        'exchange' => 'demo-exchange',
                        'auto_setup_fabric' => true
                    ),
                )
            )
        );

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $components = $this->components = new TestAsset\ProducerAbstractServiceFactory();
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
