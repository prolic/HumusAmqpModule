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

use HumusAmqpModule\Amqp\RpcServer;
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

        /* @var $request \Zend\Console\Request */

        $rpcServerName = $request->getParam('name');

        if (!$this->getRpcServerManager()->has($rpcServerName)) {
            $this->getConsole()->writeLine(
                'ERROR: RPC-Server "' . $rpcServerName . '" not found',
                ColorInterface::RED
            );
            return null;
        }

        $debug = $request->getParam('debug') || $request->getParam('d');

        if ($debug && !defined('AMQP_DEBUG')) {
            define('AMQP_DEBUG', true);
        }

        $amount = $request->getParam('amount', 0);

        if (!is_numeric($amount)) {
            $this->getConsole()->writeLine(
                'Error: amount should be null or greater than 0',
                ColorInterface::RED
            );
        } else {
            $this->rpcServer = $this->getRpcServerManager()->get($rpcServerName);
            $this->rpcServer->start($amount);
        }
    }

    public function stopRpcServer()
    {
        $this->rpcServer->forceStopConsumer();
    }

    /**
     * @todo: return response without exit call
     */
    public function shutdownRpcServer()
    {
        $this->stopRpcServer();
        exit;
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
