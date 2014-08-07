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
use Zend\Console\Prompt;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class PurgeConsumerController extends AbstractConsoleController implements ConsumerManagerAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $consumerManager;

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        /* @var $request \Zend\Console\Request */

        $consumerName = $request->getParam('consumer-name');
        $consumerManager = $this->getConsumerManager();

        if (!$consumerManager->has($consumerName)) {
            $this->getConsole()->writeLine(
                'ERROR: Consumer "' . $consumerName . '" not found',
                ColorInterface::RED
            );
            return null;
        }

        $confirm = new Prompt\Confirm('Are you sure you want to purge? [y/n]', 'y', 'n');
        $confirm->setConsole($this->getConsole());

        if ($request->getParam('no-confirmation', false)
            || $confirm->show()
        ) {
            $consumer = $consumerManager->get($consumerName);
            $consumer->purge();
            $this->getConsole()->writeLine(
                'OK',
                ColorInterface::GREEN
            );
        } else {
            $this->getConsole()->writeLine(
                'Purging cancelled!',
                ColorInterface::YELLOW
            );
        }
    }

    /**
     * @param ServiceLocatorInterface $manager
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
