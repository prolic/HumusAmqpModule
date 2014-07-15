<?php

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
