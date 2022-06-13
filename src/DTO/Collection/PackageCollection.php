<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO\Collection;

use Illuminate\Support\Collection;
use SergeevPasha\Dellin\DTO\Package;

class PackageCollection extends Collection
{

    /**
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new static(
            array_map(fn($item) => Package::fromArray($item), $data['packages'])
        );
    }

    public function offsetGet($key): Package
    {
        return parent::offsetGet($key);
    }
}
