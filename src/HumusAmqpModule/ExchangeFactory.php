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
use AMQPExchange;

class ExchangeFactory implements ExchangeFactoryInterface
{
    /**
     * @param ExchangeSpecification $specification
     * @param AMQPChannel $channel
     * @param bool $autoDeclare
     * @return AMQPExchange
     */
    public function create(ExchangeSpecification $specification, AMQPChannel $channel, $autoDeclare = true)
    {
        $exchange = new AMQPExchange($channel);
        $exchange->setArguments($specification->getArguments());
        $exchange->setName($specification->getName());
        $exchange->setFlags($specification->getFlags());
        $exchange->setType($specification->getType());

        if ($autoDeclare) {
            $exchange->declareExchange();

            // rabbitmq extension: exchange to exchange bindings
            foreach ($specification->getExchangeBindings() as $exchangeName => $routingKeys) {
                foreach ($routingKeys as $routingKey) {
                    $exchange->bind($exchangeName, $routingKey, $specification->getFlags());
                }
            }
        }

        return $exchange;
    }
}
