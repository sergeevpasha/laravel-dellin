<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class Package extends DataTransferObject
{
    /**
     * @var string
     */
    public string $uid;

    /**
     * @var int|null
     */
    public ?int $count;

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
                'uid'   => $data['uid'] ?? null,
                'count' => isset($data['count']) ? (int) $data['count'] : null,
            ]
        );
    }
}
