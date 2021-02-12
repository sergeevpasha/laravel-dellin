<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use SergeevPasha\Dellin\Enum\PaymentType;
use Spatie\DataTransferObject\DataTransferObject;

class Payment extends DataTransferObject
{
    /**
     * @var string
     */
    public string $paymentCity;

    /**
     * @var \SergeevPasha\Dellin\Enum\PaymentType
     */
    public PaymentType $type;

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
            'paymentCity' => (string) $data['payment_city'] ?? null,
            'type'        => PaymentType::fromValue((int) $data['payment_type']),
        ]);
    }
}
