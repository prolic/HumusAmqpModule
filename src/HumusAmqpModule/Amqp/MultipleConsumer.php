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
use Zend\Config\Processor\Queue;

class MultipleConsumer extends Consumer
{
    protected $queues = array();

    public function getQueueConsumerTag($queue)
    {
        return sprintf('%s-%s', $this->getConsumerTag(), $queue);
    }

    public function setQueues(array $queues)
    {
        $this->queues = $queues;
    }

    protected function setupConsumer()
    {
        if ($this->autoSetupFabric) {
            $this->setupFabric();
        }

        foreach ($this->queues as $name => $options) {
            //PHP 5.3 Compliant
            $currentObject = $this;

            $this->getChannel()->basic_consume(
                $name,
                $this->getQueueConsumerTag($name),
                false,
                false,
                false,
                false,
                function (AMQPMessage $msg) use ($currentObject, $name) {
                    $this->processQueueMessage($name, $msg);
                }
            );
        }
    }

    protected function queueDeclare()
    {
        foreach ($this->queues as $name => $options) {
            if (!$options instanceof QueueOptions) {
                $options = new QueueOptions($options);
            }
            list($queueName, ,) = $this->getChannel()->queue_declare(
                $name,
                $options->getPassive(),
                $options->getDurable(),
                $options->getExclusive(),
                $options->getAutoDelete(),
                $options->getNowait(),
                $options->getArguments(),
                $options->getTicket()
            );

            if (count($options->getRoutingKeys()) > 0) {
                foreach ($options->getRoutingKeys() as $routingKey) {
                    $this->getChannel()->queue_bind($queueName, $this->exchangeOptions->getName(), $routingKey);
                }
            } else {
                $this->getChannel()->queue_bind($queueName, $this->exchangeOptions->getName(), $this->routingKey);
            }
        }

        $this->queueDeclared = true;
    }

    /**
     * @param string $queueName
     * @param AMQPMessage $msg
     * @throws Exception\QueueNotFoundException
     */
    public function processQueueMessage($queueName, AMQPMessage $msg)
    {
        if (!isset($this->queues[$queueName])) {
            throw new Exception\QueueNotFoundException();
        }

        $processFlag = call_user_func($this->queues[$queueName]->getCallback(), $msg);

        $this->handleProcessMessage($msg, $processFlag);
    }
}
