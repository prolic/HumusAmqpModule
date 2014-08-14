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

use HumusAmqpModule\Amqp\ConsumerInterface;
use HumusAmqpModule\Exception;
use HumusAmqpModule\Amqp\Consumer;
use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class ConsumerController extends AbstractConsoleController implements ConsumerManagerAwareInterface
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $consumerManager;

    /**
     * @var ConsumerInterface
     */
    protected $consumer;

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);
        /* @var $request \Zend\Console\Request */

        if (!defined('AMQP_DEBUG') && ($request->getParam('debug') || $request->getParam('d'))) {
            define('AMQP_DEBUG', true);
        }

        if (extension_loaded('signal_handler')) {
            attach_signal(SIGTERM, array($this, 'shutdownConsumer'));
            attach_signal(SIGINT, array($this, 'shutdownConsumer'));
            attach_signal(SIGUSR1, array($this, 'stopConsumer'));
        }

        $cm = $this->getConsumerManager();

        $name = $request->getParam('name');

        if (!$cm->has($name)) {
            $this->getConsole()->writeLine(
                'Error: unknown consumer "' . $name . '"',
                ColorInterface::RED
            );

            return null;
        }

        $this->consumer = $cm->get($request->getParam('name'));

        if (!$this->consumer) {
            return null;
        }

        $this->consumer->setMemoryLimit($request->getParam('memory_limit'));
        $this->consumer->setRoutingKey($request->getParam('route'));

        $amount = $request->getParam('amount', 0);

        if (!is_numeric($amount)) {
            $this->getConsole()->writeLine(
                'Error: amount should be null or greater than 0',
                ColorInterface::RED
            );

            return null;
        }

        $this->consumer->consume($amount);
    }

    public function stopConsumer()
    {
        $this->consumer->forceStopConsumer();
    }

    public function shutdownConsumer()
    {
        echo 'received shutdown signal' . "\n";
        $this->stopConsumer();
        exit;
    }

    /**
     * @param ServiceLocatorInterface $manager
     * @return void
     */
    public function setConsumerManager(ServiceLocatorInterface $manager)
    {
        $this->consumerManager = $manager;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getConsumerManager()
    {
        return $this->consumerManager;
    }
}
