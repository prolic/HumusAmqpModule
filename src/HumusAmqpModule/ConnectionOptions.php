<?php

namespace HumusAmqpModule;

use Zend\Stdlib\AbstractOptions;

class ConnectionOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $host = 'localhost';

    /**
     * @var int
     */
    protected $port = 5672;

    /**
     * @var string
     */
    protected $login = 'guest';

    /**
     * @var string
     */
    protected $password = 'guest';

    /**
     * @var string
     */
    protected $vhost = '/';

    /**
     * @var bool
     */
    protected $persistent = false;

    /**
     * @var float
     */
    protected $readTimeout = 1.00; // secs

    /**
     * @var float
     */
    protected $writeTimeout = 1.00; // secs

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param boolean $persistent
     */
    public function setPersistent($persistent)
    {
        $this->persistent = $persistent;
    }

    /**
     * @return boolean
     */
    public function getPersistent()
    {
        return $this->persistent;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param float $readTimeout
     */
    public function setReadTimeout($readTimeout)
    {
        $this->readTimeout = $readTimeout;
    }

    /**
     * @return float
     */
    public function getReadTimeout()
    {
        return $this->readTimeout;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $vhost
     */
    public function setVhost($vhost)
    {
        $this->vhost = $vhost;
    }

    /**
     * @return string
     */
    public function getVhost()
    {
        return $this->vhost;
    }

    /**
     * @param float $writeTimeout
     */
    public function setWriteTimeout($writeTimeout)
    {
        $this->writeTimeout = $writeTimeout;
    }

    /**
     * @return float
     */
    public function getWriteTimeout()
    {
        return $this->writeTimeout;
    }
}
