<?php

namespace HumusAmqpModule\Amqp;

use PhpAmqpLib\Message\AMQPMessage;

interface MultipleConsumerInterface extends ConsumerInterface
{
    /**
     * @param array|\Traversable $queues
     * @throws Exception\InvalidArgumentException
     */
    public function setQueues($queues);

    /**
     * @return QueueOptions[]
     */
    public function getQueues();

    /**
     * @param string $queue
     * @return string
     */
    public function getQueueConsumerTag($queue);

    /**
     * @param string $queueName
     * @param AMQPMessage $msg
     * @throws Exception\QueueNotFoundException
     */
    public function processQueueMessage($queueName, AMQPMessage $msg);
}
