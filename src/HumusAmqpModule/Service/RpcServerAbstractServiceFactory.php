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

use HumusAmqpModule\RpcServer;
use HumusAmqpModule\Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Psr\Log;
use Zend\Log\PsrLoggerAdapter;
use Zend\Log\LoggerInterface as ZendLoggerInterface;

/**
 * Class RpcServerAbstractServiceFactory
 * @package HumusAmqpModule\Service
 */
class RpcServerAbstractServiceFactory extends AbstractAmqpQueueAbstractServiceFactory
{
    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'rpc_servers';

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @return RpcServer
     * @throws Exception\InvalidArgumentException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // get global service locator, if we are in a plugin manager
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator();
        }

        $spec = $this->getSpec($container, $requestedName, $requestedName);
        $this->validateSpec($container, $spec, $requestedName);

        $connection = $this->getConnection($container, $spec);
        $channel    = $this->createChannel($connection, $spec);

        $queueSpec = $this->getQueueSpec($container, $spec['queue']);
        $queue     = $this->getQueue($queueSpec, $channel, $this->useAutoSetupFabric($spec));

        $idleTimeout = array_key_exists('idle_timeout', $spec) ? $spec['idle_timeout'] : 5.0;
        $waitTimeout = array_key_exists('wait_timeout', $spec) ? $spec['wait_timeout'] : 100000;

        $rpcServer = new RpcServer($queue, $idleTimeout, $waitTimeout);

        if (array_key_exists('logger', $spec)) {
            if (!$container->has($spec['logger'])) {
                throw new Exception\InvalidArgumentException(
                    'The logger ' . $spec['logger'] . ' is not configured'
                );
            }
            $logger = $container->get($spec['logger']);
            if ($logger instanceof ZendLoggerInterface) {
                $logger = new PsrLoggerAdapter($logger);
            }
            if (!$logger instanceof Log\LoggerInterface) {
                throw new Exception\InvalidArgumentException('The logger ' . $spec['logger'] . ' is not a Psr\Log');
            }
            $rpcServer->setLogger($logger);
        } else {
            $rpcServer->setLogger($this->getDefaultNullLogger());
        }

        $callbackManager = $this->getCallbackManager($container);

        /** @var callable $callback */
        $callback = $callbackManager->get($spec['callback']);

        $rpcServer->setDeliveryCallback($callback);

        if (array_key_exists('error_callback', $spec)) {
            if (!$callbackManager->has($spec['error_callback'])) {
                throw new Exception\InvalidArgumentException(
                    'The required callback ' . $spec['error_callback'] . ' can not be found'
                );
            }
            /** @var callable $errorCallback */
            $errorCallback = $callbackManager->get($spec['error_callback']);
            $rpcServer->setFlushCallback($errorCallback);
        }

        return $rpcServer;
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

        if (!array_key_exists('callback', $spec)) {
            throw new Exception\InvalidArgumentException('Callback is missing for rpc server ' . $requestedName);
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

    /**
     * @return Log\LoggerInterface
     */
    protected function getDefaultNullLogger()
    {
        return new Log\NullLogger();
    }
}
