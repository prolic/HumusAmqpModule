<?php

/**
 * This file is part of `prolic/humus-amqp-module`.
 * (c) 2015-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace HumusAmqpModuleTest\Container;

use HumusAmqpModule\Container\CliFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class CliFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_console_runner(): void
    {
        $container = $this->prophesize(ContainerInterface::class);

        $factory = new CliFactory();
        $runner = $factory($container->reveal());

        $this->assertInstanceOf(Application::class, $runner);
    }
}
