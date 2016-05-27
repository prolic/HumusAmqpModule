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
use AMQPQueue;
use HumusAmqpModule\Exception;
use HumusAmqpModule\PluginManager;
use HumusAmqpModule\QueueFactory;
use HumusAmqpModule\QueueFactoryInterface;
use HumusAmqpModule\QueueSpecification;
use Interop\Container\ContainerInterface;

/**
 * Class AbstractAmqpQueueAbstractServiceFactory
 * @package HumusAmqpModule\Service
 */
abstract class AbstractAmqpQueueAbstractServiceFactory extends AbstractAmqpAbstractServiceFactory
{
    /**
     * @var Callback
     */
    protected $callbackManager;

    /**
     * @var QueueFactoryInterface
     */
    protected $queueFactory;

    /**
     * @param QueueSpecification $spec
     * @param AMQPChannel $channel
     * @param $autoSetupFabric
     * @return AMQPQueue
     */
    protected function getQueue(QueueSpecification $spec, AMQPChannel $channel, $autoSetupFabric)
    {
        return $this->getQueueFactory()->create($spec, $channel, $autoSetupFabric);
    }

    /**
     * @param ContainerInterface $container
     * @return PluginManager\Callback
     * @throws Exception\RuntimeException
     */
    protected function getCallbackManager(ContainerInterface $container)
    {
        if (null !== $this->callbackManager) {
            return $this->callbackManager;
        }

        if (!$container->has(PluginManager\Callback::class)) {
            throw new Exception\RuntimeException(sprintf('%s not found', PluginManager\Callback::class));
        }

        $this->callbackManager = $container->get(PluginManager\Callback::class);
        return $this->callbackManager;
    }

    /**
     * @return QueueFactoryInterface
     */
    public function getQueueFactory()
    {
        if (null === $this->queueFactory) {
            $this->setQueueFactory(new QueueFactory());
        }
        return $this->queueFactory;
    }

    /**
     * @param QueueFactoryInterface $queueFactory
     */
    public function setQueueFactory(QueueFactoryInterface $queueFactory)
    {
        $this->queueFactory = $queueFactory;
    }

    /**
     * @param ContainerInterface $container
     * @param string             $queueName
     * @return QueueSpecification
     */
    protected function getQueueSpec(ContainerInterface $container, $queueName)
    {
        $config  = $this->getConfig($container);
        return new QueueSpecification($config['queues'][$queueName]);
    }
}
