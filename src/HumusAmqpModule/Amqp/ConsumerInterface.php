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

use PhpAmqpLib\Message\AMQPMessage;

interface ConsumerInterface
{
    /**
     * Flag for message ack
     */
    const MSG_ACK = 1;

    /**
     * Flag single for message nack and requeue
     */
    const MSG_SINGLE_NACK_REQUEUE = 2;

    /**
     * Flag for reject and requeue
     */
    const MSG_REJECT_REQUEUE = 0;

    /**
     * Flag for reject and drop
     */
    const MSG_REJECT = -1;

    /**
     * Start consumer
     *
     * @param int $msgAmount
     */
    public function start($msgAmount = 0);

    /**
     * Stop consuming
     *
     * @return void
     */
    public function stopConsuming();

    /**
     * Set consumer tag
     *
     * @param string $tag
     * @return void
     */
    public function setConsumerTag($tag);

    /**
     * Get consumer tag
     *
     * @return null|string
     */
    public function getConsumerTag();

    /**
     * Force stop consumer
     *
     * @return void
     */
    public function forceStopConsumer();

    /**
     * @param callable $callback
     * @return void
     */
    public function setCallback($callback);

    /**
     * @param ExchangeOptions|array|\Traversable $options
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function setExchangeOptions($options);

    /**
     * Sets the qos settings for the current channel
     * Consider that prefetchSize and global do not work with rabbitMQ version <= 8.0
     *
     * @param array|\Traversable|QosOptions $options
     */
    public function setQosOptions($options);

    /**
     * @param QueueOptions|array|\Traversable $options
     * @return void
     */
    public function setQueueOptions($options);

    /**
     * Disables the automatic SetupFabric when using a consumer or producer
     *
     * @return void
     */
    public function disableAutoSetupFabric();

    /**
     * Set idle timeout
     *
     * @param int $idleTimeout
     * @return void
     */
    public function setIdleTimeout($idleTimeout);

    /**
     * Get idle timeout
     *
     * @return int
     */
    public function getIdleTimeout();
}
