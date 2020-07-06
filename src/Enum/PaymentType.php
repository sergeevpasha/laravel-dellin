<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Enum;

use BenSampo\Enum\Enum;

final class PaymentType extends Enum
{
    public const CASH = 0;
    public const NONCASH = 1;
}
