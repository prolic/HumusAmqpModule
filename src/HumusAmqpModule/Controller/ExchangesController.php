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
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class ExchangesController extends AbstractConsoleController
{
    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        $config = $this->getServiceLocator()->get('Config');
        $moduleConfig = $config['humus_amqp_module'];

        $this->getConsole()->writeLine('List of all exchanges', ColorInterface::GREEN);

        $exchanges = array();
        foreach ($moduleConfig as $type) {
            foreach ($type as $configPart) {
                if (!is_array($configPart)) continue;
                foreach ($configPart as $key => $value) {
                    if ($key == 'exchange_options') {
                        $exchanges[$value['type']][] = $value['name'];
                    }
                }
            }
        }

        foreach ($exchanges as $type => $values) {
            $this->getConsole()->writeLine('Exchange-Type: ' . $type, ColorInterface::GREEN);
            foreach ($values as $value) {
                $this->getConsole()->writeLine($value);
            }
        }
    }
}
