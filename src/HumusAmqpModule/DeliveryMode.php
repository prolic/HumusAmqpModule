<?php

namespace HumusAmqpModule;

use MabeEnum\Enum;

final class DeliveryMode extends Enum
{
    const DELIVERY_MODE_NON_PERSISTENT = 1;
    const DELIVERY_MODE_PERSISTENT     = 2;
}
