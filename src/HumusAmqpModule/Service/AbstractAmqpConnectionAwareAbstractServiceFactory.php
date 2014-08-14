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
 * and is licensed under the MIT license.
 */

namespace HumusAmqpModule\Service;

use HumusAmqpModule\Exception;
use Traversable;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractAmqpConnectionAwareAbstractServiceFactory extends AbstractAmqpAbstractServiceFactory
{
    /**
     * @var \HumusAmqpModule\PluginManager\Connection
     */
    protected $connectionManager;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \HumusAmqpModule\PluginManager\Connection
     * @throws Exception\RuntimeException
     */
    protected function getConnectionManager(ServiceLocatorInterface $serviceLocator)
    {
        if (null !== $this->connectionManager) {
            return $this->connectionManager;
        }

        if (!$serviceLocator->has('HumusAmqpModule\PluginManager\Connection')) {
            throw new Exception\RuntimeException(
                'HumusAmqpModule\PluginManager\Connection not found'
            );
        }

        $this->connectionManager = $serviceLocator->get('HumusAmqpModule\PluginManager\Connection');
        return $this->connectionManager;
    }
}
