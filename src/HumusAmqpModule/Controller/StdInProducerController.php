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

use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class StdInProducerController extends AbstractConsoleController
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $producerManager;

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);
        /* @var $request \Zend\Console\Request */
        /* @var $response \Zend\Console\Response */

        $producerName = $request->getParam('name');
        $producerManager = $this->getProducerManager();

        if (!$producerManager->has($producerName)) {
            $this->getConsole()->writeLine(
                'ERROR: Producer "' . $producerName . '" not found',
                ColorInterface::RED
            );

            $response->setErrorLevel(1);
            return;
        }

        $debug = $request->getParam('debug') || $request->getParam('d');

        if ($debug && !defined('AMQP_DEBUG')) {
            define('AMQP_DEBUG', true);
        }

        $producer = $producerManager->get($producerName);

        $route = $request->getParam('route', '');
        $msg = trim($request->getParam('msg'));

        $producer->publish($msg, $route);
    }

    /**
     * @param ServiceLocatorInterface $producerManager
     */
    public function setProducerManager(ServiceLocatorInterface $producerManager)
    {
        $this->producerManager = $producerManager;
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getProducerManager()
    {
        return $this->producerManager;
    }
}
