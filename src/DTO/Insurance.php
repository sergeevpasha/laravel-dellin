<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class Insurance extends DataTransferObject
{
    /**
     * @var float
     */
    public float $statedValue;

    /**
     * @var bool
     */
    public bool $term;

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
            'statedValue' => (float) $data['insurance_value'],
            'term'        => isset($data['insurance_term']) ? (bool) $data['insurance_term'] : true,
        ]);
    }
}
