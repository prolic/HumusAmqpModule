<?php

namespace HumusAmqpModule\Amqp;

use AMQPConnection;

/**
 * Persistent connection class to override method calls to automatically
 * call pconnect / pdisconnect instead of connect / disconnect, so application code
 * does not need to take care what type of connection is used
 */
class PersistentAmqpConnection extends AMQPConnection
{
    /**
     * Proxies the connect method to pconnect
     *
     * @return bool
     */
    public function connect()
    {
        return parent::pconnect();
    }

    /**
     * Proxies the disconnect method to pdisconnect
     *
     * @return bool
     */
    public function disconnect()
    {
        return parent::pdisconnect();
    }
}
