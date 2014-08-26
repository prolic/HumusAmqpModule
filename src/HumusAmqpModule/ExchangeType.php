<?php

namespace HumusAmqpModule;

use MabeEnum\Enum;

final class ExchangeType extends Enum
{
    const AMQP_EX_TYPE_DIRECT = 'direct';
    const AMQP_EX_TYPE_TOPIC = 'topic';
    const AMQP_EX_TYPE_FANOUT = 'fanout';
    const AMQP_EX_TYPE_HEADERS = 'headers';
}
