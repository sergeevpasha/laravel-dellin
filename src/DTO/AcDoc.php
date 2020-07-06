<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use SergeevPasha\Dellin\Enum\AcDocType;
use Spatie\DataTransferObject\DataTransferObject;

class AcDoc extends DataTransferObject
{
    /**
     * @var \SergeevPasha\Dellin\Enum\AcDocType
     */
    public $action;

    /**
     * From Value.
     *
     * @param int $acDocType
     *
     * @return self
     */
    public static function fromValue(int $acDocType): self
    {
        return new self([
            'action' => AcDocType::fromValue($acDocType),
        ]);
    }
}
