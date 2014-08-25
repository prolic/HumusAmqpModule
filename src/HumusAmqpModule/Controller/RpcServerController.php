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
use HumusAmqpModule\RpcServer;
use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class RpcServerController extends AbstractConsoleController
{
    /**
     * @var RpcServer
     */
    protected $rpcServer;

    /**
     * @var ServiceLocatorInterface
     */
    protected $rpcServerManager;

    /**
     * {@inheritdoc}
     *
     * @todo: handle unix signals
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

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

        pcntl_signal(SIGTERM, array($this, 'stopRpcServer'));
        pcntl_signal(SIGINT, array($this, 'stopRpcServer'));
        pcntl_signal(SIGHUP, array($this, 'stopRpcServer'));

        /* @var $request \Zend\Console\Request */
        /* @var $response \Zend\Console\Response */

        $rpcServerName = $request->getParam('name');

        if (!$this->getRpcServerManager()->has($rpcServerName)) {
            $this->getConsole()->writeLine(
                'ERROR: RPC-Server "' . $rpcServerName . '" not found',
                ColorInterface::RED
            );
            $response->setErrorLevel(1);
            return;
        }

        $debug = $request->getParam('debug') || $request->getParam('d');

        if ($debug && !defined('AMQP_DEBUG')) {
            define('AMQP_DEBUG', true);
        }

        $amount = $request->getParam('amount', 0);

        if (!is_numeric($amount)) {
            $this->getConsole()->writeLine(
                'Error: Expected integer for amount',
                ColorInterface::RED
            );
            $response->setErrorLevel(1);
            return;
        } else {
            $this->rpcServer = $this->getRpcServerManager()->get($rpcServerName);
            $this->rpcServer->consume($amount);
        }
    }

    /**
     * @return void
     */
    public function stopRpcServer()
    {
        $this->rpcServer->handleShutdownSignal();
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setRpcServerManager(ServiceLocatorInterface $serviceLocator)
    {
        $this->rpcServerManager = $serviceLocator;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getRpcServerManager()
    {
        return $this->rpcServerManager;
    }
}
