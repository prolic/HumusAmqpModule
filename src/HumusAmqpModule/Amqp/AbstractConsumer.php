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

namespace HumusAmqpModule\Amqp;

abstract class AbstractConsumer extends AbstractAmqp
{
    /**
     * @var int
     */
    protected $target;

    /**
     * @var int
     */
    protected $consumed = 0;

    /**
     * @var callback
     */
    protected $callback;

    /**
     * @var bool
     */
    protected $forceStop = false;

    /**
     * @var int
     */
    protected $idleTimeout = 0;

    /**
     * Set callback
     *
     * @param $callback
     * @return void
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Start consumer
     *
     * @param int $msgAmount
     */
    public function start($msgAmount = 0)
    {
        $this->target = $msgAmount;

        $this->setupConsumer();

        while (count($this->getChannel()->callbacks)) {
            $this->getChannel()->wait();
        }
    }

    /**
     * Stop consuming
     *
     * @return void
     */
    public function stopConsuming()
    {
        $this->getChannel()->basic_cancel($this->getConsumerTag());
    }

    /**
     * Setup consumer
     *
     * @return void
     */
    protected function setupConsumer()
    {
        if ($this->autoSetupFabric) {
            $this->setupFabric();
        }
        $this->getChannel()->basic_consume($this->queueOptions->getName(), $this->getConsumerTag(), false, false, false, false, array($this, 'processMessage'));
    }

    /**
     * Maybe stop consumer
     *
     * @return void
     * @throws Exception\BadFunctionCallException
     */
    protected function maybeStopConsumer()
    {
        if (extension_loaded('pcntl') && (defined('AMQP_WITHOUT_SIGNALS') ? !AMQP_WITHOUT_SIGNALS : true)) {
            if (!function_exists('pcntl_signal_dispatch')) {
                throw new Exception\BadFunctionCallException("Function 'pcntl_signal_dispatch' is referenced in the php.ini 'disable_functions' and can't be called.");
            }

            pcntl_signal_dispatch();
        }

        if ($this->forceStop || ($this->consumed == $this->target && $this->target > 0)) {
            $this->stopConsuming();
        } else {
            return;
        }
    }

    /**
     * Set consumer tag
     *
     * @param string $tag
     * @return void
     */
    public function setConsumerTag($tag)
    {
        $this->consumerTag = $tag;
    }

    /**
     * Get consumer tag
     *
     * @return null|string
     */
    public function getConsumerTag()
    {
        return $this->consumerTag;
    }

    /**
     * Force stop consumer
     *
     * @return void
     */
    public function forceStopConsumer()
    {
        $this->forceStop = true;
    }

    /**
     * Sets the qos settings for the current channel
     * Consider that prefetchSize and global do not work with rabbitMQ version <= 8.0
     *
     * @param array|\Traversable|QosOptions $options
     */
    public function setQosOptions($options)
    {
        if (!$options instanceof QosOptions) {
            $options = new QosOptions($options);
        }
        $this->getChannel()->basic_qos($options->getPrefetchSize(), $options->getPrefetchCount(), $options->getGlobal());
    }

    /**
     * Set idle timeout
     *
     * @param int $idleTimeout
     * @return void
     */
    public function setIdleTimeout($idleTimeout)
    {
        $this->idleTimeout = $idleTimeout;
    }

    /**
     * Get idle timeout
     *
     * @return int
     */
    public function getIdleTimeout()
    {
        return $this->idleTimeout;
    }
}
