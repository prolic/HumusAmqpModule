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
use HumusAmqpModule\RpcClient;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Class RpcClientAbstractServiceFactory
 * @package HumusAmqpModule\Service
 */
class RpcClientAbstractServiceFactory extends AbstractAmqpQueueAbstractServiceFactory
{
    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'rpc_clients';

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @return RpcClient
     * @throws Exception\InvalidArgumentException
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

        $queueSpec = $this->getQueueSpec($container, $spec['queue']);
        $queue     = $this->getQueue($queueSpec, $channel, $this->useAutoSetupFabric($spec));

        return new RpcClient($queue);
    }

    /**
     * @param ContainerInterface $container
     * @param array              $spec
     * @param string             $requestedName
     * @throws Exception\InvalidArgumentException
     */
    protected function validateSpec(ContainerInterface $container, array $spec, $requestedName)
    {
        if (!array_key_exists('queue', $spec)) {
            throw new Exception\InvalidArgumentException('Queue is missing for rpc client ' . $requestedName);
        }

        $defaultConnection = $this->getDefaultConnectionName($container);

        if (array_key_exists('connection', $spec)) {
            $connection = $spec['connection'];
        } else {
            $connection = $defaultConnection;
        }

        $config  = $this->getConfig($container);

        // validate queue existence
        if (!isset($config['queues'][$spec['queue']])) {
            throw new Exception\InvalidArgumentException(
                'The rpc client queue ' . $spec['queue'] . ' is missing in the queues configuration'
            );
        }

        // validate queue connection
        $queue = $config['queues'][$spec['queue']];
        $testConnection = array_key_exists('connection', $queue) ? $queue['connection'] : $defaultConnection;
        if ($testConnection != $connection) {
            throw new Exception\InvalidArgumentException(
                'The rpc client connection for queue ' . $spec['queue'] . ' (' . $testConnection . ') does not '
                . 'match the rpc client connection for rpc client ' . $requestedName . ' (' . $connection . ')'
            );
        }
    }
}
