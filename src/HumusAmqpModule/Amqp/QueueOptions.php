<?php

namespace HumusAmqpModule\Amqp;

use Zend\Stdlib\AbstractOptions;

class QueueOptions extends AbstractOptions
{
    protected $name = null;
    protected $passive = false;
    protected $durable = true;
    protected $exclusive = false;
    protected $auto_delete = false;
    protected $nowait = false;
    protected $arguments = null;
    protected $ticket = null;
    protected $routingKeys = array();
    protected $callback = null;

    /**
     * @param array $routingKeys
     */
    public function setRoutingKeys(array $routingKeys)
    {
        $this->routingKeys = $routingKeys;
    }

    /**
     * @return array
     */
    public function getRoutingKeys()
    {
        return $this->routingKeys;
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
     * @param boolean $exclusive
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;
    }

    /**
     * @return boolean
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

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

    /**
     * @param callable $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return null|callable
     */
    public function getCallback()
    {
        return $this->callback;
    }
}
