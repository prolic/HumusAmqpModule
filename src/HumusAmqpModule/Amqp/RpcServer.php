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

class RpcServer extends AbstractConsumer
{
    public function initServer($name)
    {
        $this->setExchangeOptions(array('name' => $name, 'type' => 'direct'));
        $this->setQueueOptions(array('name' => $name . '-queue'));
    }

    public function processMessage(AMQPMessage $msg)
    {
        try {
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            $result = call_user_func($this->callback, $msg);
            $this->sendReply(serialize($result), $msg->get('reply_to'), $msg->get('correlation_id'));
            $this->consumed++;
            $this->maybeStopConsumer();
        } catch (\Exception $e) {
            $this->sendReply('error: ' . $e->getMessage(), $msg->get('reply_to'), $msg->get('correlation_id'));
        }
    }

    protected function sendReply($result, $client, $correlationId)
    {
        $reply = new AMQPMessage($result, array('content_type' => 'text/plain', 'correlation_id' => $correlationId));
        $this->getChannel()->basic_publish($reply, '', $client);
    }
}
