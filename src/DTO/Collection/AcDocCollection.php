<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO\Collection;

use SergeevPasha\Dellin\DTO\AcDoc;
use Illuminate\Support\Collection;

class AcDocCollection extends Collection
{
    /**
     * Create AcDoc Collection from AcDoc array
     *
     * @param AcDoc[] $data
     *
     * @return self
     */
    public static function create(array $data): self
    {
        return new static(array_map(fn(AcDoc $item) => $item, $data));
    }

    public function offsetGet($key): AcDoc
    {
        return parent::offsetGet($key);
    }
}
