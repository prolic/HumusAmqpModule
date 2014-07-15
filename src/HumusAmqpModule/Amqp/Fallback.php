<?php

namespace HumusAmqpModule\Amqp;

class Fallback
{
    public function publish()
    {
        return false;
    }
}
