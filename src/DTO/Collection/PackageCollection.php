<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO\Collection;

use SergeevPasha\Dellin\DTO\Package;
use Spatie\DataTransferObject\DataTransferObjectCollection;

final class PackageCollection extends DataTransferObjectCollection
{
    public function current(): Package
    {
        return parent::current();
    }

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
}
