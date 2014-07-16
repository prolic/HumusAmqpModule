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

/**
 * Prodcuer, that publishes AMQP Messages
 */
class Producer extends AbstractAmqp
{
    protected $contentType = 'text/plain';
    protected $deliveryMode = 2;

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function setDeliveryMode($deliveryMode)
    {
        $this->deliveryMode = $deliveryMode;

        return $this;
    }

    protected function getBasicProperties()
    {
        return array('content_type' => $this->contentType, 'delivery_mode' => $this->deliveryMode);
    }

    /**
     * Publishes the message and merges additional properties with basic properties
     *
     * @param string $msgBody
     * @param string $routingKey
     * @param array $additionalProperties
     */
    public function publish($msgBody, $routingKey = '', $additionalProperties = array())
    {
        if ($this->autoSetupFabric) {
            $this->setupFabric();
        }

        $msg = new AMQPMessage((string) $msgBody, array_merge($this->getBasicProperties(), $additionalProperties));
        $this->getChannel()->basic_publish($msg, $this->exchangeOptions->getName(), (string) $routingKey);
    }

    /**
     * Publishes the message in basic batch and merges additional properties with basic properties
     *
     * @param $msgBody
     * @param string $routingKey
     * @param array $additionalProperties
     */
    public function publishBasicBatch($msgBody, $routingKey = '', $additionalProperties = array())
    {
        if ($this->autoSetupFabric) {
            $this->setupFabric();
        }

        $msg = new AMQPMessage((string) $msgBody, array_merge($this->getBasicProperties(), $additionalProperties));
        $this->getChannel()->batch_basic_publish($msg, $this->exchangeOptions->getName(), (string) $routingKey);
    }

    /**
     * Publishes the message in basic batch reusing an AMQPMessage object for performance reasons
     *
     * @param AMQPMessage $msg
     * @param string $routingKey
     */
    public function publishBasicBatchMessage(AMQPMessage $msg, $routingKey = '')
    {
        if ($this->autoSetupFabric) {
            $this->setupFabric();
        }

        $this->getChannel()->batch_basic_publish($msg, $this->exchangeOptions->getName(), (string) $routingKey);
    }

    /**
     * Publishes the batch
     */
    public function publishBatch()
    {
        if ($this->autoSetupFabric) {
            $this->setupFabric();
        }

        $this->getChannel()->publish_batch();
    }

    /**
     * Return a default message then can be reused for batch publishing
     *
     * @param string $msgBody
     * @param array $additionalProperties
     * @return AMQPMessage
     */
    public function defaultMessage($msgBody = '', $additionalProperties = array())
    {
        return new AMQPMessage($msgBody, array_merge($this->getBasicProperties(), $additionalProperties));
    }
}
