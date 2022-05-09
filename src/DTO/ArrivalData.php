<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use SergeevPasha\Dellin\Enum\ShippingType;
use Spatie\DataTransferObject\DataTransferObject;

class ArrivalData extends DataTransferObject
{
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
                'variant'      => ShippingType::fromValue((int) $data['arrival_shipping_type']),
                'terminalID'   => isset($data['arrival_terminal_id']) ? +$data['arrival_terminal_id'] : null,
                'addressID'    => $data['arrival_address_id'] ?? null,
                'address'      => [
                    'street' => $data['arrival_street_code'] ?? null,
                ],
                'city'         => $data['arrival_city_code'] ?? null,
                'time'         => [
                    'worktimeStart' => $data['arrival_worktime_start'] ?? null,
                    'worktimeEnd'   => $data['arrival_worktime_end'] ?? null,
                    'breakStart'    => $data['arrival_break_start'] ?? null,
                    'breakEnd'      => $data['arrival_break_end'] ?? null,
                    'exactTime'     => $data['arrival_exact_time'] ?? null,
                ],
                'handling'     => [
                    'freightLift' => $data['arrival_freight_lift'] ?? null,
                    'toFloor'     => $data['arrival_to_floor'] ?? null,
                    'carry'       => $data['arrival_carry'] ?? null,
                ],
                'requirements' => $data['arrival_requirements'] ?? [],
            ]
        );
    }
}
