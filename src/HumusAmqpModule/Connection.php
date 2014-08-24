<?php

namespace HumusAmqp;

use AMQPConnection;

class Connection extends AMQPConnection
{
    /**
     * @var bool
     */
    protected $persistent = false;

    /**
     * @param array|\Traversable|ConnectionOptions null $options
     */
    public function __construct($options = null)
    {
        if (!$options instanceof ConnectionOptions) {
            $options = new ConnectionOptions($options);
        }

        $this->persistent = $options->getPersistent();

        $options = $options->toArray();
        unset($options['persistent']);

        parent::__construct($options);
    }

    public function connect()
    {
        if ($this->persistent) {
            $this->pconnect();
        } else {
            parent::connect();
        }
    }

    public function disconnect()
    {
        if ($this->persistent) {
            $this->pdisconnect();
        } else {
            parent::disconnect();
        }
    }
}
