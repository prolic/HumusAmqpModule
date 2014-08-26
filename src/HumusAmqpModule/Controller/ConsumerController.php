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

use HumusAmqpModule\ConsumerInterface;
use HumusAmqpModule\Exception;
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
        /* @var $response \Zend\Console\Response */

        if (!extension_loaded('pcntl')) {
            throw new Exception\ExtensionNotLoadedException(
                'pnctl extension missing'
            );
        }

        if (!function_exists('pcntl_signal')) {
            throw new Exception\BadFunctionCallException(
                "Function 'pcntl_signal' is referenced in the php.ini 'disable_functions' and can't be called."
            );
        }

        pcntl_signal(SIGTERM, array($this, 'stopConsumer'));
        pcntl_signal(SIGINT, array($this, 'stopConsumer'));
        pcntl_signal(SIGHUP, array($this, 'stopConsumer'));

        $cm = $this->getConsumerManager();

        $name = $request->getParam('name');

        if (!$cm->has($name)) {
            $this->getConsole()->writeLine(
                'Error: unknown consumer "' . $name . '"',
                ColorInterface::RED
            );

            $response->setErrorLevel(1);
            return;
        }

        $this->consumer = $cm->get($request->getParam('name'));

        $amount = $request->getParam('amount', 0);

        if (!is_numeric($amount)) {
            $this->getConsole()->writeLine(
                'Error: amount should be null or greater than 0',
                ColorInterface::RED
            );

            $response->setErrorLevel(1);
            return;
        }

        $this->consumer->consume($amount);
    }

    /**
     * Stops the consumer
     *
     * @return void
     */
    public function stopConsumer()
    {
        $this->consumer->handleShutdownSignal();
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
