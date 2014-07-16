<?php

namespace HumusAmqpModule\Amqp;

class PartsHolder
{
    /**
     * @var AbstractAmqp[]
     */
    protected $parts = array();

    /**
     * Add part
     *
     * @param string $type
     * @param AbstractAmqp $part
     * @return void
     */
    public function addPart($type, AbstractAmqp $part)
    {
        $this->parts[$type][] = $part;
    }

    /**
     * Get parts
     *
     * @param string $type
     * @return AbstractAmqp[]
     */
    public function getParts($type)
    {
        return $this->parts[(string) $type];
    }

    /**
     * Check for parts of a given type
     *
     * @param string $type
     * @return bool
     */
    public function hasParts($type)
    {
        return isset($this->parts[(string) $type]);
    }
}
