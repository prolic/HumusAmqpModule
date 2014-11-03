<?php

namespace HumusAmqpModuleTest;

use HumusAmqpModule\ExchangeSpecification;
use PHPUnit_Framework_TestCase;

class ExchangeSpecificationTest extends PHPUnit_Framework_TestCase
{
    public function testAlternateExchangeIsPartOfArguments()
    {
        $spec = new ExchangeSpecification();
        $spec->setAlternateExchange('foobar');
        $args = $spec->getArguments();

        $this->assertArrayHasKey('alternate-exchange', $args);
        $this->assertEquals('foobar', $args['alternate-exchange']);

        $spec->setAlternateExchange(null);
        $args = $spec->getArguments();

        $this->assertArrayNotHasKey('alternate-exchange', $args);
    }
}
