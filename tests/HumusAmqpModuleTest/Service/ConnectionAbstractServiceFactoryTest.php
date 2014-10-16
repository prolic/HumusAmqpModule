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
use HumusAmqpModule\Service\ConnectionAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

class ConnectionAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var ConnectionAbstractServiceFactory
     */
    protected $components;

    public function setUp()
    {
        $config = array(
            'humus_amqp_module' => array(
                'connections' => array(
                    'default' => array(
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                        'vhost' => '/',
                    )
                )
            )
        );

        $services = $this->services = new ServiceManager();
        $services->setAllowOverride(true);
        $services->setService('Config', $config);

        $components = $this->components = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $cm = new ConnectionPluginManager());
        $cm->addAbstractFactory($components);
        $cm->setServiceLocator($services);
    }

    public function testMissingGlobalConfigIndicatesCannotCreateInstance()
    {
        $services = $this->services = new ServiceManager();
        $services->setAllowOverride(true);

        $components = $this->components = new ConnectionAbstractServiceFactory();
        $services->setService('HumusAmqpModule\PluginManager\Connection', $cm = new ConnectionPluginManager());
        $cm->addAbstractFactory($components);
        $cm->setServiceLocator($services);

        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
    }

    public function testMissingConfigServiceIndicatesCannotCreateInstance()
    {
        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
        // second call give more code coverage (test lazy loading)
        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
    }

    public function testMissinAmqpServicePrefixIndicatesCannotCreateInstance()
    {
        $this->services->setService('Config', array());
        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
    }

    public function testInvalidConfigIndicatesCannotCreateInstance()
    {
        $this->services->setService('Config', array('humus_amqp_module' => 'string'));
        $this->assertFalse($this->components->canCreateServiceWithName($this->services, 'foo', 'foo'));
    }

    public function testEmptyConnectionConfigIndicatesCannotCreateConnection()
    {
        $this->services->setService('Config', array('humus_amqp_module' => array()));
        $this->assertFalse(
            $this->components->canCreateServiceWithName($this->services, 'test-connection', 'test-connection')
        );
    }

    public function testMissingSpecIndicatesCanCreateConnectionWithDefaultSettings()
    {
        $this->services->setService('Config', array(
            'humus_amqp_module' => array(
                'connections' => array(
                    'test-connection' => array()
                ),
            ),
        ));
        $this->assertTrue(
            $this->components->canCreateServiceWithName(
                $this->services,
                'test-connection',
                'test-connection'
            )
        );
    }

    public function testInvalidConnectionConfigIndicatesCannotCreateConnection()
    {
        $this->services->setService('Config', array(
            'humus_amqp_module' => array(
                'connections' => array(
                    'test-connection' => 'foobar'
                ),
            ),
        ));
        $this->assertFalse(
            $this->components->canCreateServiceWithName(
                $this->services,
                'test-connection',
                'test-connection'
            )
        );
    }

    public function testCorrectConfigIndicatesCanCreateConnection()
    {
        $this->services->setService('Config', array(
            'humus_amqp_module' => array(
                'connections' => array(
                    'test-connection' => array(
                    )
                ),
            ),
        ));
        $this->assertTrue(
            $this->components->canCreateServiceWithName(
                $this->services,
                'test-connection',
                'test-connection'
            )
        );
    }

    public function testPersistentConnectionSettings()
    {
        $this->services->setService('Config', array(
            'humus_amqp_module' => array(
                'connections' => array(
                    'test-connection' => array(
                        'persistent' => true
                    )
                ),
            ),
        ));
        $conn = $this->components->createServiceWithName($this->services, 'testconnection', 'test-connection');
        $this->assertInstanceOf('AMQPConnection', $conn);
        $this->assertTrue(isset($conn->persistent));
        $this->assertTrue($conn->persistent);
    }
}
