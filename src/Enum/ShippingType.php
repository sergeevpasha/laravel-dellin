<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Enum;

use BenSampo\Enum\Enum;

final class ShippingType extends Enum
{
    public const ADDRESS = 0;
    public const TERMINAL = 1;
}
