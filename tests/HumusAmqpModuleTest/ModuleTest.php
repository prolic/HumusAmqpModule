<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license
 */

namespace HumusSupervisorModuleTest;

use HumusAmqpModule\Module;
use HumusAmqpModuleTest\ServiceManagerTestCase;
use Zend\Mvc\MvcEvent;

class ModuleTest extends ServiceManagerTestCase
{
    public function testGetConfig()
    {
        $module = new Module();

        $config = $module->getConfig();

        $this->assertInternalType('array', $config);
        $this->assertSame($config, unserialize(serialize($config)));
    }

    public function testGetAutoloaderConfig()
    {
        $module = new Module();

        $config = $module->getAutoloaderConfig();

        $this->assertInternalType('array', $config);
        $this->assertSame($config, unserialize(serialize($config)));
    }

    public function testGetConsoleUsage()
    {
        $module = new Module();

        $usage = $module->getConsoleUsage($this->getMock('Zend\Console\Adapter\AdapterInterface'));

        $this->assertInternalType('array', $usage);
    }

    public function testPluginManagers()
    {
        $sm = $this->getServiceManager();
        $app = $sm->get('Application');

        $event = new MvcEvent();
        $event->setApplication($app);

        $module = new Module();
        $module->onBootstrap($event);

        $connectionManager = $sm->get('HumusAmqpModule\PluginManager\Connection');
        $this->assertInstanceOf('HumusAmqpModule\PluginManager\Connection', $connectionManager);
    }
}
