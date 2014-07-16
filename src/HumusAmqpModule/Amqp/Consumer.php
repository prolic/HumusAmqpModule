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

class Consumer extends AbstractConsumer
{
    /**
     * @var int $memoryLimit
     */
    protected $memoryLimit = null;

    /**
     * Set the memory limit
     *
     * @param int $memoryLimit
     */
    public function setMemoryLimit($memoryLimit)
    {
        $this->memoryLimit = $memoryLimit;
    }

    /**
     * Get the memory limit
     *
     * @return int
     */
    public function getMemoryLimit()
    {
        return $this->memoryLimit;
    }

    /**
     * Consume the message
     *
     * @param int $msgAmount
     */
    public function consume($msgAmount)
    {
        $this->target = $msgAmount;

        $this->setupConsumer();

        while (count($this->getChannel()->callbacks)) {
            $this->maybeStopConsumer();
            $this->getChannel()->wait(null, false, $this->getIdleTimeout());
        }
    }

    /**
     * Purge the queue
     */
    public function purge()
    {
        $this->getChannel()->queue_purge($this->queueOptions->getName(), true);
    }

    public function processMessage(AMQPMessage $msg)
    {
        $processFlag = call_user_func($this->callback, $msg);

        $this->handleProcessMessage($msg, $processFlag);
    }

    protected function handleProcessMessage(AMQPMessage $msg, $processFlag)
    {
        if ($processFlag === ConsumerInterface::MSG_REJECT_REQUEUE || false === $processFlag) {
            // Reject and requeue message to RabbitMQ
            $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], true);
        } else if ($processFlag === ConsumerInterface::MSG_SINGLE_NACK_REQUEUE) {
            // NACK and requeue message to RabbitMQ
            $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'], false, true);
        } else if ($processFlag === ConsumerInterface::MSG_REJECT) {
            // Reject and drop
            $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], false);
        } else {
            // Remove message from queue only if callback return not false
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        }

        $this->consumed++;
        $this->maybeStopConsumer();

        if (!is_null($this->getMemoryLimit()) && $this->isRamAlmostOverloaded()) {
            $this->stopConsuming();
        }
    }

    /**
     * Checks if memory in use is greater or equal than memory allowed for this process
     *
     * @return boolean
     */
    protected function isRamAlmostOverloaded()
    {
        if (memory_get_usage(true) >= ($this->getMemoryLimit() * 1024 * 1024)) {
            return true;
        } else {
            return false;
        }
    }
}
