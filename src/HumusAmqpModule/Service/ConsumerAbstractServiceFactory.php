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

use HumusAmqpModule\Consumer;
use HumusAmqpModule\Exception;
use Interop\Container\ContainerInterface;
use Zend\Log\LoggerInterface as ZendLoggerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Psr\Log;
use Zend\Log\PsrLoggerAdapter;

/**
 * Class ConsumerAbstractServiceFactory
 * @package HumusAmqpModule\Service
 */
class ConsumerAbstractServiceFactory extends AbstractAmqpQueueAbstractServiceFactory
{
    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'consumers';

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @return Consumer
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

        $config = $this->getConfig($container);
        $queues = [];

        foreach ($spec['queues'] as $queue) {
            if ($this->useAutoSetupFabric($spec)) {
                // will create the exchange to declare it on the channel
                // the created exchange will not be used afterwards
                $exchangeName = $config['queues'][$queue]['exchange'];
                $this->getExchange($container, $channel, $exchangeName, $this->useAutoSetupFabric($spec));
            }

            $queueSpec = $this->getQueueSpec($container, $queue);
            $queues[] = $this->getQueue($queueSpec, $channel, $this->useAutoSetupFabric($spec));
        }

        $idleTimeout = array_key_exists('idle_timeout', $spec) ? $spec['idle_timeout'] : 5.0;
        $waitTimeout = array_key_exists('wait_timeout', $spec) ? $spec['wait_timeout'] : 100000;

        $consumer = new Consumer($queues, $idleTimeout, $waitTimeout);

        if (array_key_exists('logger', $spec)) {
            if (!$container->has($spec['logger'])) {
                throw new Exception\InvalidArgumentException('The logger ' . $spec['logger'] . ' is not configured');
            }
            $logger = $container->get($spec['logger']);
            if ($logger instanceof ZendLoggerInterface) {
                $logger = new PsrLoggerAdapter($logger);
            }
            if (!$logger instanceof Log\LoggerInterface) {
                throw new Exception\InvalidArgumentException('The logger ' . $spec['logger'] . ' is not a Psr\Log');
            }
            $consumer->setLogger($logger);
        } else {
            $consumer->setLogger($this->getDefaultNullLogger());
        }

        $callbackManager = $this->getCallbackManager($container);

        if (!$callbackManager->has($spec['callback'])) {
            throw new Exception\InvalidArgumentException(
                'The required callback ' . $spec['callback'] . ' can not be found'
            );
        }
        /** @var callable $callback */
        $callback        = $callbackManager->get($spec['callback']);
        $consumer->setDeliveryCallback($callback);

        if (array_key_exists('flush_callback', $spec)) {
            if (!$callbackManager->has($spec['flush_callback'])) {
                throw new Exception\InvalidArgumentException(
                    'The required callback ' . $spec['flush_callback'] . ' can not be found'
                );
            }
            /** @var callable $flushCallback */
            $flushCallback = $callbackManager->get($spec['flush_callback']);
            $consumer->setFlushCallback($flushCallback);
        }

        if (array_key_exists('error_callback', $spec)) {
            if (!$callbackManager->has($spec['error_callback'])) {
                throw new Exception\InvalidArgumentException(
                    'The required callback ' . $spec['error_callback'] . ' can not be found'
                );
            }
            /** @var callable $errorCallback */
            $errorCallback = $callbackManager->get($spec['error_callback']);
            $consumer->setErrorCallback($errorCallback);
        }

        return $consumer;
    }

    /**
     * @param ContainerInterface $container
     * @param array              $spec
     * @param string             $requestedName
     * @throws Exception\InvalidArgumentException
     */
    protected function validateSpec(ContainerInterface $container, array $spec, $requestedName)
    {
        // queues are required
        if (!array_key_exists('queues', $spec)) {
            throw new Exception\InvalidArgumentException(
                'Queues are missing for consumer ' . $requestedName
            );
        }

        // callback is required
        if (!array_key_exists('callback', $spec)) {
            throw new Exception\InvalidArgumentException(
                'No delivery callback specified for consumer ' . $requestedName
            );
        }

        $defaultConnection = $this->getDefaultConnectionName($container);

        if (array_key_exists('connection', $spec)) {
            $connection = $spec['connection'];
        } else {
            $connection = $defaultConnection;
        }

        $config  = $this->getConfig($container);
        foreach ($spec['queues'] as $queue) {
            // validate queue existence
            if (!isset($config['queues'][$queue])) {
                throw new Exception\InvalidArgumentException(
                    'Queue ' . $queue . ' is missing in the queue configuration'
                );
            }

            // validate queue connection
            $testConnection = isset($config['queues'][$queue]['connection'])
                ? $config['queues'][$queue]['connection']
                : $defaultConnection;

            if ($testConnection != $connection) {
                throw new Exception\InvalidArgumentException(
                    'The queue connection for queue ' . $queue . ' (' . $testConnection . ') does not '
                    . 'match the consumer connection for consumer ' . $requestedName . ' (' . $connection . ')'
                );
            }

            // exchange binding is required
            if (!isset($config['exchanges'][$config['queues'][$queue]['exchange']])) {
                throw new Exception\InvalidArgumentException(
                    'The queues exchange ' . $config['queues'][$queue]['exchange']
                    . ' is missing in the exchanges configuration'
                );
            }

            // validate exchange connection
            $exchange = $config['exchanges'][$config['queues'][$queue]['exchange']];
            $testConnection = array_key_exists('connection', $exchange) ? $exchange['connection'] : $defaultConnection;
            if ($testConnection != $connection) {
                throw new Exception\InvalidArgumentException(
                    'The exchange connection for exchange ' . $config['queues'][$queue]['exchange']
                    . ' (' . $testConnection . ') does not match the consumer connection for consumer '
                    . $requestedName . ' (' . $connection . ')'
                );
            }
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
