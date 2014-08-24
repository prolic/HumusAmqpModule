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

use AMQPChannel;
use HumusAmqpModule\ExchangeFactory;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class RpcClientAbstractServiceFactory extends AbstractAmqpConnectionAwareAbstractServiceFactory
{
    /**
     * @var string Second-level configuration key indicating connection configuration
     */
    protected $subConfigKey = 'rpc_clients';

    /**
     * @var ExchangeFactory
     */
    protected $exchangeFactory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->exchangeFactory = new ExchangeFactory();
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        // get global service locator, if we are in a plugin manager
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $spec = $this->getSpec($serviceLocator, $requestedName);

        // use default connection if nothing else present
        if (!isset($spec['connection'])) {
            $spec['connection'] = 'default';
        }

        $connectionManager = $this->getConnectionManager($serviceLocator);
        $connection = $connectionManager->get($spec['connection']);

        $qosOptions = $this->getQosOptions($serviceLocator, $requestedName);

        $channel = new AMQPChannel($connection);
        $channel->setPrefetchCount($qosOptions->getPrefetchCount());
        $channel->setPrefetchSize($qosOptions->getPrefetchSize());

        $exchangeSpec = $this->getExchangeSpec($serviceLocator, $spec['exchange']);
        $exchange = $this->exchangeFactory->create($exchangeSpec, $channel, $this->useAutoSetupFabric($spec));

        $connection = $connectionManager->get($spec['connection']);
        $rpcClient = new $class($connection);

        return $rpcClient;
    }
}
