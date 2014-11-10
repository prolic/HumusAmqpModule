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

namespace HumusAmqpModule;

use AMQPChannel;
use AMQPQueue;

class QueueFactory implements QueueFactoryInterface
{
    /**
     * @param QueueSpecification $specification
     * @param AMQPChannel $channel
     * @param bool $autoDeclare
     * @return AMQPQueue
     */
    public function create(QueueSpecification $specification, AMQPChannel $channel, $autoDeclare = true)
    {
        $queue = new AMQPQueue($channel);
        if ($specification->getName() != '') {
            $queue->setName($specification->getName());
        }
        $queue->setFlags($specification->getFlags());
        $queue->setArguments($specification->getArguments());

        if ($autoDeclare) {
            $queue->declareQueue();

            $routingKeys = $specification->getRoutingKeys();
            if (empty($routingKeys)) {
                $queue->bind($specification->getExchange(), null, $specification->getBindArguments());
            } else {
                foreach ($routingKeys as $routingKey) {
                    $queue->bind($specification->getExchange(), $routingKey, $specification->getBindArguments());
                }
            }
        }

        return $queue;
    }
}
