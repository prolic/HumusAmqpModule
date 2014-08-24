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
use Zend\ServiceManager\ServiceLocatorInterface;

// @todo: make traits !!
abstract class AbstractAmqpCallbackAwareAbstractServiceFactory extends AbstractAmqpConnectionAwareAbstractServiceFactory
{
    /**
     * @var \HumusAmqpModule\PluginManager\Callback
     */
    protected $callbackManager;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \HumusAmqpModule\PluginManager\Callback
     * @throws Exception\RuntimeException
     */
    protected function getCallbackManager(ServiceLocatorInterface $serviceLocator)
    {
        if (null !== $this->callbackManager) {
            return $this->callbackManager;
        }

        if (!$serviceLocator->has('HumusAmqpModule\PluginManager\Callback')) {
            throw new Exception\RuntimeException(
                'HumusAmqpModule\PluginManager\Callback not found'
            );
        }

        $this->callbackManager = $serviceLocator->get('HumusAmqpModule\PluginManager\Callback');
        return $this->callbackManager;
    }
}
