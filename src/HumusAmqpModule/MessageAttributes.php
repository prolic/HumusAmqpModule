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

use Zend\Stdlib\AbstractOptions;

/**
 * Class MessageAttributes
 * @package HumusAmqpModule
 */
class MessageAttributes extends AbstractOptions
{
    const DELIVERY_MODE_NON_PERSISTENT = 1;

    const DELIVERY_MODE_PERSISTENT = 2;

    /**
     * @var string
     */
    protected $contentType = 'text/plain';

    /**
     * @var string
     */
    protected $contentEncoding;

    /**
     * @var array
     */
    protected $headers = array();

    /**
     * @var int
     */
    protected $deliveryMode = self::DELIVERY_MODE_PERSISTENT;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var string
     */
    protected $correlationId;

    /**
     * @var string
     */
    protected $replyTo;

    /**
     * @var int
     */
    protected $expiration;

    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var string
     */
    protected $timestamp;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @var string
     */
    protected $appId;

    /**
     * @var string
     */
    protected $clusterId;

    /**
     * @var bool
     */
    protected $mandatory = false;

    /**
     * @var bool
     */
    protected $immediate = false;

    /**
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param string $clusterId
     */
    public function setClusterId($clusterId)
    {
        $this->clusterId = $clusterId;
    }

    /**
     * @return string
     */
    public function getClusterId()
    {
        return $this->clusterId;
    }

    /**
     * @param string $contentEncoding
     */
    public function setContentEncoding($contentEncoding)
    {
        $this->contentEncoding = $contentEncoding;
    }

    /**
     * @return string
     */
    public function getContentEncoding()
    {
        return $this->contentEncoding;
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $correlationId
     */
    public function setCorrelationId($correlationId)
    {
        $this->correlationId = $correlationId;
    }

    /**
     * @return string
     */
    public function getCorrelationId()
    {
        return $this->correlationId;
    }

    /**
     * @param int $deliveryMode
     * @throws Exception\InvalidArgumentException
     */
    public function setDeliveryMode($deliveryMode)
    {
        if (!in_array($deliveryMode, array(self::DELIVERY_MODE_NON_PERSISTENT, self::DELIVERY_MODE_PERSISTENT))) {
            throw new Exception\InvalidArgumentException(
                'delivery mode must be one of 1 or 2, ' . $deliveryMode . ' given'
            );
        }
        $this->deliveryMode = $deliveryMode;
    }

    /**
     * @return int
     */
    public function getDeliveryMode()
    {
        return $this->deliveryMode;
    }

    /**
     * @param int $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $messageId
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $replyTo
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
    }

    /**
     * @return string
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @param string $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param boolean $immediate
     */
    public function setImmediate($immediate)
    {
        $this->immediate = $immediate;
    }

    /**
     * @return boolean
     */
    public function getImmediate()
    {
        return $this->immediate;
    }

    /**
     * @param boolean $mandatory
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    /**
     * @return boolean
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * @return int
     */
    public function getFlags()
    {
        $flags = 0;
        $flags |= $this->getMandatory() ? AMQP_MANDATORY : 0;
        $flags |= $this->getImmediate() ? AMQP_IMMEDIATE : 0;

        return $flags;
    }

    /**
     * Cast to array
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $transform = function ($letters) {
            $letter = array_shift($letters);
            return '_' . strtolower($letter);
        };
        foreach ($this as $key => $value) {
            if ($key === '__strictMode__') {
                continue;
            }
            if ($value === null) {
                continue;
            }
            $normalizedKey = preg_replace_callback('/([A-Z])/', $transform, $key);
            $array[$normalizedKey] = $value;
        }
        unset($array['mandatory']);
        unset($array['immediate']);
        return $array;
    }
}
