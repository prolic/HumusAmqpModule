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

use HumusAmqpModule\Exception;
use HumusAmqpModule\Amqp\Consumer;
use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class ConsumerController extends AbstractConsoleController
{

    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        if (false === defined('AMQP_WITHOUT_SIGNALS')) {
            define('AMQP_WITHOUT_SIGNALS', $request->getParam('without-signals'));
        }

        if (!AMQP_WITHOUT_SIGNALS && extension_loaded('pcntl')) {
            if (!function_exists('pcntl_signal')) {
                throw new Exception\BadFunctionCallException("Function 'pcntl_signal' is referenced in the php.ini 'disable_functions' and can't be called.");
            }

            pcntl_signal(SIGTERM, array(&$this, 'stopConsumer'));
            pcntl_signal(SIGINT, array(&$this, 'stopConsumer'));
            pcntl_signal(SIGHUP, array(&$this, 'restartConsumer'));
        }

        if (!defined('AMQP_DEBUG') && ($request->getParam('debug') || $request->getParam('d'))) {
            define('AMQP_DEBUG', true);
        }

        if (!$this->consumer = $this->loadConsumer($this->getServiceLocator(), $request->getParam('name'))) {
            return null;
        }

        $this->consumer->setMemoryLimit($request->getParam('memory_limit'));
        $this->consumer->setRoutingKey($request->getParam('route'));

        $amount = $request->getParam('amount', 0);

        if (!is_numeric($amount)) {
            return $this->getConsole()->writeLine('Error: amount should be null or greater than 0', ColorInterface::RED);
        }

        $this->consumer->consume($amount);
    }

    protected function loadConsumer(ServiceLocatorInterface $serviceLocator, $name)
    {
        if (!$serviceLocator->has($name)) {
            $this->getConsole()->writeLine('Error: unknown consumer "' . $name .'"', ColorInterface::RED);
            return null;
        }

        $consumer = $serviceLocator->get($name);

        if (!$consumer instanceof Consumer) {
            $this->getConsole()->writeLine('Error: "' . $name. '" is not a consumer', ColorInterface::RED);
            return null;
        }

        return $consumer;
    }

    public function stopConsumer()
    {
        if ($this->consumer instanceof Consumer) {
            $this->consumer->forceStopConsumer();
        } else {
            exit();
        }
    }

    public function restartConsumer()
    {
        // TODO: Implement restarting of consumer
    }
}
