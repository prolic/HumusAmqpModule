<?php

namespace HumusAmqpModule\Amqp;

use Zend\Stdlib\AbstractOptions;

class ExchangeOptions extends AbstractOptions
{
    protected $name = '';
    protected $type = '';
    protected $passive = false;
    protected $durable = true;
    protected $auto_delete = false;
    protected $internal = false;
    protected $nowait = false;
    protected $arguments = null;
    protected $ticket = null;
    protected $declare = true;

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param array|null $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return array|null
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param boolean $auto_delete
     */
    public function setAutoDelete($auto_delete)
    {
        $this->auto_delete = $auto_delete;
    }

    /**
     * @return boolean
     */
    public function getAutoDelete()
    {
        return $this->auto_delete;
    }

    /**
     * @param boolean $declare
     */
    public function setDeclare($declare)
    {
        $this->declare = $declare;
    }

    /**
     * @return boolean
     */
    public function getDeclare()
    {
        return $this->declare;
    }

    /**
     * @param boolean $durable
     */
    public function setDurable($durable)
    {
        $this->durable = $durable;
    }

    /**
     * @return boolean
     */
    public function getDurable()
    {
        return $this->durable;
    }

    /**
     * @param boolean $internal
     */
    public function setInternal($internal)
    {
        $this->internal = $internal;
    }

    /**
     * @return boolean
     */
    public function getInternal()
    {
        return $this->internal;
    }

    /**
     * @param boolean $nowait
     */
    public function setNowait($nowait)
    {
        $this->nowait = $nowait;
    }

    /**
     * @return boolean
     */
    public function getNowait()
    {
        return $this->nowait;
    }

    /**
     * @param boolean $passive
     */
    public function setPassive($passive)
    {
        $this->passive = $passive;
    }

    /**
     * @return boolean
     */
    public function getPassive()
    {
        return $this->passive;
    }

    /**
     * @param string|null $ticket
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * @return string|null
     */
    public function getTicket()
    {
        return $this->ticket;
    }
}
