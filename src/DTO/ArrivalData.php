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
    public $variant;

    /**
     * @var string|null
     */
    public ?string $terminalID;

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
            'variant'    => ShippingType::fromValue((int) $data['arrival_shipping_type']),
            'terminalID' => isset($data['arrival_terminal_id']) ? $data['arrival_terminal_id'] : null,
            'addressID'  => isset($data['arrival_address_id']) ? $data['arrival_address_id'] : null,
            'address'    => [
                'street' => isset($data['arrival_street_code']) ? $data['arrival_street_code'] : null,
            ],
            'city' => isset($data['arrival_city_code']) ? $data['arrival_city_code'] : null,
            'time' => [
                'worktimeStart' => isset($data['arrival_worktime_start']) ? $data['arrival_worktime_start'] : null,
                'worktimeEnd'   => isset($data['arrival_worktime_end']) ? $data['arrival_worktime_end'] : null,
                'breakStart'    => isset($data['arrival_break_start']) ? $data['arrival_break_start'] : null,
                'breakEnd'      => isset($data['arrival_break_end']) ? $data['arrival_break_end'] : null,
                'exactTime'     => isset($data['arrival_exact_time']) ? $data['arrival_exact_time'] : null,
            ],
            'handling' => [
                'freightLift' => isset($data['arrival_freight_lift']) ? $data['arrival_freight_lift'] : null,
                'toFloor'     => isset($data['arrival_to_floor']) ? $data['arrival_to_floor'] : null,
                'carry'       => isset($data['arrival_carry']) ? $data['arrival_carry'] : null,
            ],
            'requirements' => isset($data['arrival_requirements']) ? $data['arrival_requirements'] : [],
        ]);
    }
}
