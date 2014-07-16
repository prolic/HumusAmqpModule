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

use Indigo\Supervisor\Supervisor;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class SupervisorController extends AbstractConsoleController
{
    /**
     * @var Supervisor
     */
    protected $supervisor;

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        parent::dispatch($request, $response);

        $action = $request->getParam('action');

        $this->$action();
    }

    /**
     * @return void
     */
    public function start()
    {
        $this->supervisor->startAllProcesses();
    }

    /**
     * @return void
     */
    public function stop()
    {
        $this->supervisor->stopAllProcesses();
    }

    /**
     * @return void
     */
    public function processlist()
    {
        $processes = $this->supervisor->getAllProcesses();

        $table = new \Zend\Text\Table\Table(array('columnWidths' => array(40, 9, 20)));

        $row = new \Zend\Text\Table\Row();
        $row->createColumn('Process name', array('align' => 'center'));
        $row->createColumn('Running', array('align' => 'center'));
        $row->createColumn('memory usage', array('align' => 'center'));
        $table->appendRow($row);
        $table->setPadding(1);
        foreach ($processes as $process) {
            $row = new \Zend\Text\Table\Row();
            $row->createColumn($process->getName());
            $running = $process->isRunning() ? 'yes' : 'no';
            $row->createColumn($running);
            $row->createColumn((string) $process->getMemUsage());
            $table->appendRow($row);
        }
        echo $table;
    }

    /**
     * @return void
     */
    public function pid()
    {
        echo $this->supervisor->getPID();
    }

    /**
     * @return void
     */
    public function version()
    {
        echo $this->supervisor->getVersion();
    }

    /**
     * @return void
     */
    public function api()
    {
        echo $this->supervisor->getAPIVersion();
    }

    /**
     * @return void
     */
    public function isLocal()
    {
        if ($this->supervisor->isLocal()) {
            echo 'local';
        } else {
            echo 'remote';
        }
    }

    /**
     * @param Supervisor $supervisor
     */
    public function setSupervisor(Supervisor $supervisor)
    {
        $this->supervisor = $supervisor;
    }
}
