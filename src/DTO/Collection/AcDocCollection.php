<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO\Collection;

use SergeevPasha\Dellin\DTO\AcDoc;
use Spatie\DataTransferObject\DataTransferObjectCollection;

final class AcDocCollection extends DataTransferObjectCollection
{
    public function current(): AcDoc
    {
        return parent::current();
    }

    /**
     * Create AcDoc Collection fro mAcDoc array
     *
     * @param  AcDoc[] $data
     * @return self
     */
    public static function create(array $data): self
    {
        return new static(array_map(fn(AcDoc $item) => $item, $data));
    }
}
