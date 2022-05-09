<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use Carbon\Carbon;
use Spatie\DataTransferObject\DataTransferObject;

class DellinTrack extends DataTransferObject
{
    /**
     * @var string
     */
    public string $status;

    /**
     * @var \Carbon\Carbon
     */
    public Carbon $startDate;

    /**
     * @var \Carbon\Carbon
     */
    public Carbon $receiveDate;

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
                'status'      => $data['state_name'] ?? '',
                'start_date'  => isset($data['order_dates']['derrival_from_osp_sender']) ?
                    Carbon::parse($data['order_dates']['derrival_from_osp_sender']) :
                    null,
                'receiveDate' => isset($data['order_dates']['arrival_to_osp_receiver']) ?
                    Carbon::parse($data['order_dates']['arrival_to_osp_receiver']) :
                    null,
            ]
        );
    }
}
