<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class Cargo extends DataTransferObject
{
    /**
     * @var int
     */
    public int $quantity;

    /**
     * @var float
     */
    public float $length;

    /**
     * @var float
     */
    public float $width;

    /**
     * @var float
     */
    public float $height;

    /**
     * @var float
     */
    public float $weight;

    /**
     * @var float
     */
    public float $totalVolume;

    /**
     * @var float
     */
    public float $totalWeight;

    /**
     * @var float|null
     */
    public ?float $oversizedWeight;

    /**
     * @var float|null
     */
    public ?float $oversizedVolume;

    /**
     * @var string|null
     */
    public ?string $freightUID;

    /**
     * @var string|null
     */
    public ?string $freightName;

    /**
     * @var float
     */
    public float $hazardClass;

    /**
     * @var \SergeevPasha\Dellin\DTO\Insurance|null
     */
    public $insurance;

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
            'quantity'        => isset($data['cargo_quantity']) ? (int) $data['cargo_quantity'] : 1,
            'length'          => isset($data['cargo_length']) ? (float) $data['cargo_length'] : 0.3,
            'width'           => isset($data['cargo_width']) ? (float) $data['cargo_width'] : 0.21,
            'height'          => isset($data['cargo_height']) ? (float) $data['cargo_height'] : 0.5,
            'weight'          => isset($data['cargo_weight']) ? (float) $data['cargo_weight'] : 0.01,
            'totalVolume'     => isset($data['cargo_total_volume']) ? (float) $data['cargo_total_volume'] : 0.001,
            'totalWeight'     => isset($data['cargo_total_weight']) ? (float) $data['cargo_total_weight'] : 0.5,
            'oversizedWeight' => isset($data['cargo_oversized_weight']) ?
                (float) $data['cargo_oversized_weight'] : null,
            'oversizedVolume' => isset($data['cargo_oversized_volume']) ?
                (float) $data['cargo_oversized_volume'] : null,
            'freightUID'  => isset($data['cargo_freight_uid']) ? (string) $data['cargo_freight_uid'] : null,
            'freightName' => isset($data['cargo_freight_name']) ? (string) $data['cargo_freight_name'] : null,
            'hazardClass' => isset($data['cargo_hazard_class']) ? (float) $data['cargo_hazard_class'] : (float) 0,
            'insurance'   => isset($data['insurance_value']) ? Insurance::fromArray($data) : null,
        ]);
    }
}
