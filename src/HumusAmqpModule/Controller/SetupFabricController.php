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

use HumusAmqpModule\Amqp\PartsHolder;
use Zend\Console\ColorInterface;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class SetupFabricController extends AbstractConsoleController
{
    /**
     * @var PartsHolder
     */
    protected $partsHolder;

    /**
     * @param PartsHolder $partsHolder
     */
    public function setPartsHolder(PartsHolder $partsHolder)
    {
        $this->partsHolder = $partsHolder;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        $debug = $request->getParam('debug') || $request->getParam('d');

        if ($debug && !defined('AMQP_DEBUG')) {
            define('AMQP_DEBUG', true);
        }

        $this->console->writeLine('Setting up the AMQP fabric');

        $that = $this;

        array_map(
            function ($name) use ($that) {
                if ($that->partsHolder->hasParts($name)) {
                    $that->console->write('Declaring exchanges and queues for ' . $name . ' ');
                    foreach ($that->partsHolder->getParts($name) as $part) {
                        $part->setupFabric();
                    }
                    $that->console->writeLine('OK', ColorInterface::GREEN);
                } else {
                    $that->console->writeLine('No ' . $name . ' found to configure', ColorInterface::YELLOW);
                }
            },
            array(
                'consumers',
                'multiple_consumers',
                'anon_consumers',
                'rpc_servers',
                'producers'
            )
        );

        $this->console->writeLine('DONE', ColorInterface::GREEN);
    }
}
