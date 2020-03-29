<?php

/**
 * This file is part of `prolic/humus-amqp-module`.
 * (c) 2015-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace HumusAmqpModule;

use Humus\Amqp\Container\DriverFactory;
use Humus\Amqp\Driver\Driver;

return [
    'service_manager' => [
        'factories' => [
            'humus_amqp_cli' => Container\CliFactory::class,
            Driver::class => DriverFactory::class,
        ],
    ],
];
