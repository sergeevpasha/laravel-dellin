<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use SergeevPasha\Dellin\Enum\ShippingType;
use Spatie\DataTransferObject\DataTransferObject;

class DerivalData extends DataTransferObject
{
    /**
     * @var string
     */
    public string $produceDate;

    /**
     * @var \SergeevPasha\Dellin\Enum\ShippingType
     */
    public ShippingType $variant;

    /**
     * @var int|null
     */
    public ?int $terminalID;

    /**
     * @var string|null
     */
    public ?string $addressID;

    /**
     * @var array<mixed>
     */
    public array $address;

    /**
     * @var string|null
     */
    public ?string $city;

    /**
     * @var array<mixed>
     */
    public array $time;

    /**
     * @var array<mixed>
     */
    public array $handling;

    /**
     * @var array<mixed>
     */
    public array $requirements;

    /**
     * From Array.
     *
     * @param array<mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self([
            'produceDate' => $data['derival_produce_date'],
            'variant'     => ShippingType::fromValue((int) $data['derival_shipping_type']),
            'terminalID'  => isset($data['derival_terminal_id']) ? $data['derival_terminal_id'] : null,
            'addressID'   => isset($data['derival_address_id']) ? $data['derival_address_id'] : null,
            'address'     => [
                'street' => isset($data['derival_street_code']) ? $data['derival_street_code'] : null,
            ],
            'city' => isset($data['derival_city_code']) ? $data['derival_city_code'] : null,
            'time' => [
                'worktimeStart' => isset($data['derival_worktime_start']) ? $data['derival_worktime_start'] : null,
                'worktimeEnd'   => isset($data['derival_worktime_end']) ? $data['derival_worktime_end'] : null,
                'breakStart'    => isset($data['derival_break_start']) ? $data['derival_break_start'] : null,
                'breakEnd'      => isset($data['derival_break_end']) ? $data['derival_break_end'] : null,
                'exactTime'     => isset($data['derival_exact_time']) ? $data['derival_exact_time'] : null,
            ],
            'handling' => [
                'freightLift' => isset($data['derival_freight_lift']) ? $data['derival_freight_lift'] : null,
                'toFloor'     => isset($data['derival_to_floor']) ? $data['derival_to_floor'] : null,
                'carry'       => isset($data['derival_carry']) ? $data['derival_carry'] : null,
            ],
            'requirements' => isset($data['derival_requirements']) ? $data['derival_requirements'] : [],
        ]);
    }
}
