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

use HumusAmqpModule\Exception;
use HumusAmqpModule\Producer;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class ProducerAbstractServiceFactory
 * @package HumusAmqpModule\Service
 */
class ProducerAbstractServiceFactory extends AbstractAmqpAbstractServiceFactory
{
    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'producers';

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @return Producer
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator();
        }

        $spec       = $this->getSpec($container, $requestedName, $requestedName);
        $this->validateSpec($container, $spec, $requestedName);
        $connection = $this->getConnection($container, $spec);
        $channel    = $this->createChannel($connection, $spec);

        $exchange = $this->getExchange($container, $channel, $spec['exchange'], $this->useAutoSetupFabric($spec));
        return new Producer($exchange);
    }

    /**
     * @param ContainerInterface $container
     * @param array              $spec
     * @param string             $requestedName
     * @throws Exception\InvalidArgumentException
     */
    protected function validateSpec(ContainerInterface $container, array $spec, $requestedName)
    {
        $defaultConnection = $this->getDefaultConnectionName($container);

        if (isset($spec['connection'])) {
            $connection = $spec['connection'];
        } else {
            $connection = $defaultConnection;
        }

        // exchange required
        if (!array_key_exists('exchange', $spec)) {
            throw new Exception\InvalidArgumentException(
                'Exchange is missing for producer ' . $requestedName
            );
        }

        $exchange = $spec['exchange'];
        $config  = $this->getConfig($container);
        // validate exchange existence
        if (!isset($config['exchanges'][$exchange])) {
            throw new Exception\InvalidArgumentException(
                'The producer exchange ' . $exchange . ' is missing in the exchanges configuration'
            );
        }

        // validate exchange connection
        $testConnection = isset($config['exchanges'][$exchange]['connection'])
            ? $config['exchanges'][$exchange]['connection']
            : $this->getDefaultConnectionName($container);

        if ($testConnection != $connection) {
            throw new Exception\InvalidArgumentException(
                'The producer connection for exchange ' . $exchange . ' (' . $testConnection . ') does not '
                . 'match the producer connection for producer ' . $requestedName . ' (' . $connection . ')'
            );
        }
    }
}
