<?php

namespace HumusAmqpModuleTest\Amqp;

use HumusAmqpModule\Amqp\Fallback;

class FallbackTest extends \PHPUnit_Framework_TestCase
{
    public function testFallback()
    {
        $fallback = new Fallback();
        $this->assertFalse($fallback->publish());
    }
}
