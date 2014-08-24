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

namespace HumusAmqpModule\Service;

use HumusAmqpModule\ExchangeSpecification;
use HumusAmqpModule\PluginManager\Connection as ConnectionManager;
use HumusAmqpModule\QosOptions;
use Traversable;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractAmqpAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string Top-level configuration key indicating amqp configuration
     */
    protected $configKey = 'humus_amqp_module';

    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = '';

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var array
     */
    protected $instances = array();

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (isset($this->instances[$requestedName])) {
            return true;
        }

        $config = $this->getConfig($serviceLocator);
        if (empty($config)) {
            return false;
        }

        if (!isset($config[$this->subConfigKey][$requestedName])) {
            return false;
        }

        $spec = $config[$this->subConfigKey][$requestedName];

        if ((is_array($spec) || $spec instanceof Traversable)) {
            return true;
        }

        return false;
    }

    /**
     * Get amqp configuration, if any
     *
     * @param  ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        // get global service locator, if we are in a plugin manager
        if ($services instanceof AbstractPluginManager) {
            $services = $services->getServiceLocator();
        }

        if (!$services->has('Config')) {
            $this->config = array();
            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config[$this->configKey])
            || !is_array($config[$this->configKey])
        ) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $exchangeName
     * @return QosOptions
     */
    protected function getQosOptions(ServiceLocatorInterface $serviceLocator, $exchangeName)
    {
        $config  = $this->getConfig($serviceLocator);
        $data = isset($config[$this->subConfigKey][$exchangeName]['qos']) ? $config[$this->subConfigKey]['qos'] : array();
        $qosOptions = new QosOptions($data);
        return $qosOptions;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $exchangeName
     * @return ExchangeSpecification
     */
    protected function getExchangeSpec(ServiceLocatorInterface $serviceLocator, $exchangeName)
    {
        $config  = $this->getConfig($serviceLocator);
        $spec = new ExchangeSpecification($config['exchanges'][$exchangeName]);
        return $spec;
    }

    /**
     * @param array $spec
     * @return bool
     */
    protected function useAutoSetupFabric(array $spec)
    {
        return (isset($spec['auto_setup_fabric']) && $spec['auto_setup_fabric']);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $requestedName
     * @return array
     */
    protected function getSpec(ServiceLocatorInterface $serviceLocator, $requestedName)
    {
        $config  = $this->getConfig($serviceLocator);
        $spec = $config[$this->subConfigKey][$requestedName];
        return $spec;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return ConnectionManager
     * @throws \HumusAmqpModule\Exception\RuntimeException
     */
    protected function getConnectionManager(ServiceLocatorInterface $serviceLocator)
    {
        if ($this->connectionManager === null) {
            if (!$serviceLocator->has('HumusAmqpModule\PluginManager\Connection')) {
                throw new Exception\RuntimeException(
                    'HumusAmqpModule\PluginManager\Connection not found'
                );
            }
            $this->connectionManager = $serviceLocator->get('HumusAmqpModule\PluginManager\Connection');
        }
        return $this->connectionManager;
    }
}
