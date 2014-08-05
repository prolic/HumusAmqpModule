<?php

namespace HumusAmqpModuleTest\Service\TestAsset;

use PhpAmqpLib\Message\AMQPMessage;

class TestCallback
{
    /**
     * @param AMQPMessage $message
     */
    public function __invoke(AMQPMessage $message)
    {
        echo $message->body . "\n";
    }
}
