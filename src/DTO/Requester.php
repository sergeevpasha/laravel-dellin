<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use SergeevPasha\Dellin\Enum\RequesterRole;
use Spatie\DataTransferObject\DataTransferObject;

class Requester extends DataTransferObject
{
    /**
     * @var \SergeevPasha\Dellin\Enum\RequesterRole
     */
    public $role;

    /**     *
     * https://dev.dellin.ru/api/auth/counteragents/.
     *
     * @var string
     */
    public string $uid;

    /**
     * From Array.
     *
     * @param int $role
     * @param string $uid
     *
     * @return self
     */
    public static function fromArray(int $role, string $uid): self
    {
        return new self([
            'role' => RequesterRole::fromValue($role),
            'uid'  => $uid,
        ]);
    }
}
