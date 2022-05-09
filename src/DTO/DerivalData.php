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
     * @var array
     */
    public array $address;

    /**
     * @var string|null
     */
    public ?string $city;

    /**
     * @var array
     */
    public array $time;

    /**
     * @var array
     */
    public array $handling;

    /**
     * @var array
     */
    public array $requirements;

    /**
     * From Array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            [
                'produceDate' => $data['derival_produce_date'],
                'variant'     => ShippingType::fromValue((int) $data['derival_shipping_type']),
                'terminalID'  => isset($data['derival_terminal_id']) ? +$data['derival_terminal_id'] : null,
                'addressID'   => $data['derival_address_id'] ?? null,
                'address'     => [
                                'street' => $data['derival_street_code'] ?? null,
                            ],
                'city' => $data['derival_city_code'] ?? null,
                'time' => [
                                'worktimeStart' => $data['derival_worktime_start'] ?? null,
                                'worktimeEnd'   => $data['derival_worktime_end'] ?? null,
                                'breakStart'    => $data['derival_break_start'] ?? null,
                                'breakEnd'      => $data['derival_break_end'] ?? null,
                                'exactTime'     => $data['derival_exact_time'] ?? null,
                            ],
                'handling' => [
                                'freightLift' => $data['derival_freight_lift'] ?? null,
                                'toFloor'     => $data['derival_to_floor'] ?? null,
                                'carry'       => $data['derival_carry'] ?? null,
                            ],
                'requirements' => $data['derival_requirements'] ?? [],
            ]
        );
    }
}
