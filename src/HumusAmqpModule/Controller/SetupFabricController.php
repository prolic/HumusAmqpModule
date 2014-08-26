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

namespace HumusAmqpModule\Controller;

use AMQPChannel;
use HumusAmqpModule\ExchangeFactory;
use HumusAmqpModule\ExchangeSpecification;
use HumusAmqpModule\PluginManager\Connection as ConnectionManager;
use HumusAmqpModule\QueueFactory;
use HumusAmqpModule\QueueSpecification;
use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class SetupFabricController extends AbstractConsoleController
{
    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ExchangeFactory
     */
    protected $exchangeFactory;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        /* @var $request \Zend\Console\Request */
        /* @var $response \Zend\Console\Response */

        $console = $this->getConsole();
        $console->writeLine('Setting up the AMQP fabric');

        $config = $this->getConfig();

        if (empty($config['exchanges'])) {
            $console->writeLine('No exchanges found to configure', ColorInterface::RED);
            $response->setErrorLevel(1);
            return;
        }

        if (empty($config['queues'])) {
            $console->writeLine('No queues found to configure', ColorInterface::RED);
            $response->setErrorLevel(1);
            return;
        }

        $console->write('Declaring exchanges ...' . PHP_EOL);
        $exchangeFactory = $this->getExchangeFactory();
        foreach ($config['exchanges'] as $name => $spec) {
            $channel = $this->createChannel($spec, $config['default_connection']);

            $spec = new ExchangeSpecification($spec);
            $exchangeFactory->create($spec, $channel, true);
        }

        $console->write('Declaring queues ...' . PHP_EOL);
        $queueFactory = $this->getQueueFactory();
        foreach ($config['queues'] as $name => $spec) {
            $channel = $this->createChannel($spec, $config['default_connection']);

            $spec = new QueueSpecification($spec);
            $queueFactory->create($spec, $channel, true);
        }

        $console->writeLine('DONE', ColorInterface::GREEN);
    }

    /**
     * @param ConnectionManager $connectionManager
     */
    public function setConnectionManager($connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * @return ConnectionManager
     */
    public function getConnectionManager()
    {
        return $this->connectionManager;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return ExchangeFactory
     */
    protected function getExchangeFactory()
    {
        if (null === $this->exchangeFactory) {
            $this->exchangeFactory = new ExchangeFactory();
        }
        return $this->exchangeFactory;
    }

    /**
     * @return QueueFactory
     */
    protected function getQueueFactory()
    {
        if (null === $this->queueFactory) {
            $this->queueFactory = new QueueFactory();
        }
        return $this->queueFactory;
    }

    /**
     * @param array $spec
     * @param string $defaultConnectionName
     * @return AMQPChannel
     */
    protected function createChannel(array $spec, $defaultConnectionName)
    {
        if (isset($spec['connection'])) {
            $connectionName = $spec['connection'];
        } else {
            $connectionName = $defaultConnectionName;
        }
        $connection = $this->getConnectionManager()->get($connectionName);
        $channel = new AMQPChannel($connection);

        return $channel;
    }
}
