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

    public function start()
    {
        $this->supervisor->startAllProcesses();
    }

    public function stop()
    {
        $this->supervisor->stopAllProcesses();
    }

    /*
     * start|stop|list|pid|version|api
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

    public function pid()
    {
        echo $this->supervisor->getPID();
    }

    public function version()
    {
        echo $this->supervisor->getVersion();
    }

    public function api()
    {
        echo $this->supervisor->getAPIVersion();
    }

    public function isLocal()
    {
        echo $this->supervisor->isLocal();
    }

    /**
     * @param Supervisor $supervisor
     */
    public function setSupervisor(Supervisor $supervisor)
    {
        $this->supervisor = $supervisor;
    }
}
