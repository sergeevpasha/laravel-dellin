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

    /**
     * Map russian service kind from Dellin API to Enum.
     *
     * @param string $serviceKind
     * @return static|null
     */
    public static function fromServiceKind(string $serviceKind): ?self
    {
        $map = [
            'авто' => self::AUTO,
            'автомобильная' => self::AUTO,
            'экспресс' => self::EXPRESS,
            'письмо' => self::LETTER,
            'авиа' => self::AVIA,
            'малогабаритный' => self::SMALL,
        ];

        $value = $map[mb_strtolower($serviceKind)] ?? null;

        return $value !== null ? new static($value) : null;
    }
}
