<?php

namespace HumusAmqpModule\Amqp;

class PartsHolder
{
    protected $parts = array();

    public function addPart($type, AbstractAmqp $part)
    {
        $this->parts[$type][] = $part;
    }

    public function getParts($type)
    {
        return $this->parts[(string) $type];
    }
}
