<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Enum;

use BenSampo\Enum\Enum;

final class AcDocType extends Enum
{
    public const SEND = 0;
    public const RETURN = 1;
}
