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

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;

abstract class AbstractAmqp
{
    protected $conn;
    protected $ch;
    protected $consumerTag;
    protected $exchangeDeclared = false;
    protected $queueDeclared = false;
    protected $routingKey = '';
    protected $autoSetupFabric = true;
    protected $basicProperties = array('content_type' => 'text/plain', 'delivery_mode' => 2);

    /**
     * @var ExchangeOptions
     */
    protected $exchangeOptions;

    /**
     * @var QueueOptions
     */
    protected $queueOptions;

    /**
     * @param AMQPConnection $conn
     * @param AMQPChannel|null $ch
     * @param null $consumerTag
     */
    public function __construct(AMQPConnection $conn, AMQPChannel $ch = null, $consumerTag = null)
    {
        $this->conn = $conn;
        $this->ch = $ch;

        if (!($conn instanceof AMQPLazyConnection)) {
            $this->getChannel();
        }

        $this->consumerTag = empty($consumerTag)
            ? sprintf("PHPPROCESS_%s_%s", gethostname(), getmypid())
            : $consumerTag;
    }

    public function __destruct()
    {
        if ($this->ch) {
            $this->ch->close();
        }

        if ($this->conn->isConnected()) {
            $this->conn->close();
        }
    }

    /**
     * @return AMQPChannel
     */
    public function getChannel()
    {
        if (empty($this->ch)) {
            $this->ch = $this->conn->channel();
        }

        return $this->ch;
    }

    /**
     * @param AMQPChannel $ch
     * @return void
     */
    public function setChannel(AMQPChannel $ch)
    {
        $this->ch = $ch;
    }

    /**
     * @param ExchangeOptions|array|\Traversable $options
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function setExchangeOptions($options)
    {
        if (!$options instanceof ExchangeOptions) {
            $options = new ExchangeOptions($options);
        }

        if (!$options->getName()) {
            throw new Exception\InvalidArgumentException('You must provide an exchange name');
        }

        if (!$options->getType()) {
            throw new Exception\InvalidArgumentException('You must provide an exchange type');
        }

        $this->exchangeOptions = $options;
    }

    /**
     * @return ExchangeOptions
     */
    public function getExchangeOptions()
    {
        if (!$this->exchangeOptions instanceof ExchangeOptions) {
            $this->exchangeOptions = new ExchangeOptions();
        }
        return $this->exchangeOptions;
    }

    /**
     * @param QueueOptions|array|\Traversable $options
     * @return void
     */
    public function setQueueOptions($options)
    {
        if (!$options instanceof QueueOptions) {
            $options = new QueueOptions($options);
        }
        $this->queueOptions = $options;
    }

    /**
     * @return QueueOptions
     */
    public function getQueueOptions()
    {
        if (!$this->queueOptions instanceof QueueOptions) {
            $this->queueOptions = new QueueOptions();
        }
        return $this->queueOptions;
    }

    /**
     * @param string $routingKey
     * @return void
     */
    public function setRoutingKey($routingKey)
    {
        $this->routingKey = $routingKey;
    }

    /**
     * @return void
     */
    protected function exchangeDeclare()
    {
        $options = $this->getExchangeOptions();

        if ($options->getDeclare()) {

            $this->getChannel()->exchange_declare(
                $options->getName(),
                $options->getType(),
                $options->getPassive(),
                $options->getDurable(),
                $options->getAutoDelete(),
                $options->getInternal(),
                $options->getNowait(),
                $options->getArguments(),
                $options->getTicket()
            );

            $this->exchangeDeclared = true;
        }
    }

    /**
     * @return void
     */
    protected function queueDeclare()
    {
        $options = $this->getQueueOptions();

        if (null !== $options->getName()) {
            list($queueName, ,) = $this->getChannel()->queue_declare(
                $options->getName(),
                $options->getPassive(),
                $options->getDurable(),
                $options->getExclusive(),
                $options->getAutoDelete(),
                $options->getNowait(),
                $options->getArguments(),
                $options->getTicket()
            );

            if (count($options->getRoutingKeys())) {
                foreach ($options->getRoutingKeys() as $routingKey) {
                    $this->getChannel()->queue_bind($queueName, $this->getExchangeOptions()->getName(), $routingKey);
                }
            } else {
                $this->getChannel()->queue_bind($queueName, $this->getExchangeOptions()->getName(), $this->routingKey);
            }

            $this->queueDeclared = true;
        }
    }

    /**
     * @return void
     */
    public function setupFabric()
    {
        if (!$this->exchangeDeclared) {
            $this->exchangeDeclare();
        }

        if (!$this->queueDeclared) {
            $this->queueDeclare();
        }
    }

    /**
     * Disables the automatic SetupFabric when using a consumer or producer
     *
     * @return void
     */
    public function disableAutoSetupFabric()
    {
        $this->autoSetupFabric = false;
    }
}
