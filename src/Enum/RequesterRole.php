<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Enum;

use BenSampo\Enum\Enum;

final class RequesterRole extends Enum
{
    public const SENDER = 0;
    public const RECEIVER = 1;
    public const PAYER = 2;
    public const THIRD = 3;
}
