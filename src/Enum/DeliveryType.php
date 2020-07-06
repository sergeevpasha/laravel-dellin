<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Enum;

use BenSampo\Enum\Enum;

final class DeliveryType extends Enum
{
    public const AUTO = 0;
    public const EXPRESS = 1;
    public const LETTER = 2;
    public const AVIA = 3;
    public const SMALL = 4;
}
