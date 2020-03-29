<?php

/**
 * This file is part of `prolic/humus-amqp-module`.
 * (c) 2015-2020 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace HumusAmqpModuleTest;

use HumusAmqpModule\Module;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_config(): void
    {
        $module = new Module();
        $config = $module->getConfig();

        if (\method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($config);
        } else {
            $this->assertInternalType('array', $config);
        }

        $this->assertSame($config, \unserialize(\serialize($config)));
    }
}
