<?php

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
        $processes = $this->supervisor->getAllProcess();

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
