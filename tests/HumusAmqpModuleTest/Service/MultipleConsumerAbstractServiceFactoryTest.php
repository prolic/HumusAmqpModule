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

use HumusAmqpModule\PluginManager\Callback as CallbackPluginManager;
use HumusAmqpModule\PluginManager\Connection as ConnectionPluginManager;
use HumusAmqpModule\PluginManager\MultipleConsumer as MultipleConsumerPluginManager;
use HumusAmqpModule\Service\ConnectionAbstractServiceFactory;
use HumusAmqpModule\Service\ConsumerAbstractServiceFactory;
use HumusAmqpModule\Service\MultipleConsumerAbstractServiceFactory;
use HumusAmqpModule\Service\ProducerAbstractServiceFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

class MultipleConsumerAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
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
                    'multiple_consumer' => 'HumusAmqpModule\Amqp\MultipleConsumer',
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
                'multiple_consumers' => array(
                    'test-consumer' => array(
                        'exchange_options' => array(
                            'name' => 'demo-exchange',
                            'type' => 'direct',
                        ),
                        'queues' => array(
                            array(
                                'name' => 'myconsumer-queue-1',
                                'callback' => __NAMESPACE__ . '\TestAsset\TestCallback',
                            ),
                            array(
                                'name' => 'myconsumer-queue-2',
                                'callback' => __NAMESPACE__ . '\TestAsset\TestCallback',
                            )
                        ),
                        'qos_options' => array(
                            'prefetchSize' => 0,
                            'prefetchCount' => 0
                        ),
                        'idle_timeout' => 20,
                        'auto_setup_fabric' => false,
                        'callback' => 'test-callback'
                    ),
                ),
            )
        );

        $services    = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $dependentComponent = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $cm = new ConnectionPluginManager());
        $cm->addAbstractFactory($dependentComponent);
        $cm->setServiceLocator($services);

        $callbackManager = new CallbackPluginManager();
        $callbackManager->setInvokableClass('test-callback', __NAMESPACE__ . '\TestAsset\TestCallback');
        $services->setService('HumusAmqpModule\PluginManager\Callback', $callbackManager);


        $callbackManager->setServiceLocator($services);

        $components = $this->components = new MultipleConsumerAbstractServiceFactory();
        $services->setService(
            'HumusAmqpModule\PluginManager\MultipleConsumer',
            $consumerManager = new MultipleConsumerPluginManager()
        );
        $consumerManager->addAbstractFactory($components);
        $consumerManager->setServiceLocator($services);
    }

    public function testCreateConsumer()
    {
        $consumer = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $consumer2 = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $this->assertNotSame($consumer, $consumer2);
        $this->assertInstanceOf('HumusAmqpModule\Amqp\MultipleConsumer', $consumer);
        /* @var $producer \HumusAmqpModule\Amqp\Producer */
        $this->assertEquals('demo-exchange', $consumer->getExchangeOptions()->getName());
        $this->assertEquals('direct', $consumer->getExchangeOptions()->getType());
        $queues = $consumer->getQueues();
        $this->assertCount(2, $queues);
        $this->assertTrue(array_key_exists('myconsumer-queue-1', $queues));
        $this->assertTrue(array_key_exists('myconsumer-queue-2', $queues));
        $queue = $queues['myconsumer-queue-1'];
        $this->assertEquals('myconsumer-queue-1', $queue->getName());
    }

    public function testCreateConsumerWithCustomClassAndWithoutConnectionName()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['multiple_consumers']['test-consumer']['class'] = __NAMESPACE__
            . '\TestAsset\CustomMultipleConsumer';
        unset($config['humus_amqp_module']['consumers']['test-consumer']['connection']);
        $this->services->setService('Config', $config);

        $consumer = $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
        $this->assertInstanceOf('HumusAmqpModuleTest\Service\TestAsset\CustomMultipleConsumer', $consumer);
    }

    public function testCreateConsumerWithInvalidConsumerClass()
    {
        $this->setExpectedException(
            'HumusAmqpModule\Exception\RuntimeException',
            'Consumer of type stdClass is invalid; must implement HumusAmqpModule\Amqp\MultipleConsumerInterface'
        );
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['multiple_consumers']['test-consumer']['class'] = 'stdClass';
        $this->services->setService('Config', $config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     * @expectedExceptionMessage Plugin of type stdClass is invalid; must be a callable
     */
    public function testCreateConsumerWithInvalidCallback()
    {
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['multiple_consumers']['test-consumer']['queues'][0]['callback'] = 'stdClass';
        $this->services->setService('Config', $config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    public function testCreateConsumerWithInvalidConnection()
    {
        $this->setExpectedException(
            'HumusAmqpModule\Exception\RuntimeException',
            'Plugin of type stdClass is invalid; must implement PhpAmqpLib\Connection\AbstractConnection'
        );
        $config = $this->services->get('Config');
        $config['humus_amqp_module']['multiple_consumers']['test-consumer']['connection'] = 'stdClass';
        $this->services->setService('Config', $config);

        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     * @expectedExceptionMessage HumusAmqpModule\PluginManager\Connection not found
     */
    public function testCreateConsumerWithoutConnectionManager()
    {
        $this->services->setService('HumusAmqpModule\\PluginManager\\Connection', null);
        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }

    /**
     * @expectedException HumusAmqpModule\Exception\RuntimeException
     * @expectedExceptionMessage HumusAmqpModule\PluginManager\Callback not found
     */
    public function testCreateConsumerWithoutCallbackManager()
    {
        $this->services->setService('HumusAmqpModule\\PluginManager\\Callback', null);
        $this->components->createServiceWithName($this->services, 'test-consumer', 'test-consumer');
    }
}
