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

class RpcClient extends AbstractAmqp
{
    /**
     * @var int
     */
    protected $requests = 0;

    /**
     * @var array
     */
    protected $replies = array();

    /**
     * @var bool
     */
    protected $expectSerializedResponse;

    /**
     * @var int
     */
    protected $timeout = 0;

    private $queueName;

    public function initClient($expectSerializedResponse = true)
    {
        $this->expectSerializedResponse = $expectSerializedResponse;
    }

    public function addRequest($msgBody, $server, $requestId = null, $routingKey = '', $expiration = 0)
    {
        if (empty($requestId)) {
            throw new Exception\InvalidArgumentException('You must provide a $requestId');
        }

        $msg = new AMQPMessage($msgBody, array('content_type' => 'text/plain',
            'reply_to' => $this->getQueueName(),
            'delivery_mode' => 1, // non durable
            'expiration' => $expiration*1000,
            'correlation_id' => $requestId));

        $this->getChannel()->basic_publish($msg, $server, $routingKey);

        $this->requests++;

        if ($expiration > $this->timeout) {
            $this->timeout = $expiration;
        }
    }

    public function getReplies()
    {
        $this->replies = array();
        $this->getChannel()->basic_consume($this->getQueueName(), '', false, true, false, false, array($this, 'processMessage'));

        while (count($this->replies) < $this->requests) {
            $this->getChannel()->wait(null, false, $this->timeout);
        }

        $this->getChannel()->basic_cancel($this->getQueueName());
        $this->requests = 0;
        $this->timeout = 0;

        return $this->replies;
    }

    public function processMessage(AMQPMessage $msg)
    {
        $messageBody = $msg->body;
        if ($this->expectSerializedResponse) {
            $messageBody = unserialize($messageBody);
        }

        $this->replies[$msg->get('correlation_id')] = $messageBody;
    }

    protected function getQueueName()
    {
        if (null === $this->queueName) {
            list($this->queueName, ,) = $this->getChannel()->queue_declare("", false, false, true, true);
        }

        return $this->queueName;
    }
}
