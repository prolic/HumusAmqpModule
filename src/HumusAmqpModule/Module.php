<?php
/**
 * Copyright (c) 2016. Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 *  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 *  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 *  A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 *  OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 *  SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 *  LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 *  DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 *  THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *  OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *  This software consists of voluntary contributions made by many individuals
 *  and is licensed under the MIT license.
 */

declare (strict_types=1);

namespace HumusAmqpModule;

use HumusAmqpModule\Console\Output\PropertyOutput;
use Symfony\Component\Console\Input\StringInput;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Module
 * @package HumusAmqpModule
 */
class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ConsoleUsageProviderInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceManager;

    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     * @return void
     */
    public function onBootstrap(EventInterface $e)
    {
        $this->serviceManager = $e->getTarget()->getServiceManager();
    }

    /**
     * Get config
     *
     * @return array|mixed|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(Console $console)
    {
        /* @var $cli \Symfony\Component\Console\Application */
        $cli    = $this->serviceManager->get('humus-amqp-cli');
        $output = new PropertyOutput();

        $cli->run(new StringInput('list'), $output);

        return $output->getMessage();
    }
}
