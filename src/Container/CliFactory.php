<?php

/**
 * This file is part of `prolic/humus-amqp-module`.
 * (c) 2015-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace HumusAmqpModule\Container;

use Humus\Amqp\Console\ConsoleRunner;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

final class CliFactory
{
    public function __invoke(ContainerInterface $container): Application
    {
        return ConsoleRunner::createApplication(ConsoleRunner::createHelperSet($container));
    }
}
