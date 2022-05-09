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
     * @var float
     */
    public float $price;

    /**
     * @var \Carbon\Carbon|null
     */
    public ?Carbon $startDate;

    /**
     * @var \Carbon\Carbon|null
     */
    public ?Carbon $receiveDate;

    /**
     * From Array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $price = 0;
        if (isset($data['documents'])) {
            $index = array_search('shipping', array_column($data['documents'], 'document_type'));
            if ($index !== false) {
                $price = isset($data['documents'][$index]['total_sum']) ?? 0;
            }
        }

        return new self(
            [
                'status'      => $data['state_name'] ?? '',
                'price'       => (float) $price,
                'startDate'   => isset($data['order_dates']['derrival_from_osp_sender']) ?
                    Carbon::parse($data['order_dates']['derrival_from_osp_sender']) :
                    null,
                'receiveDate' => isset($data['order_dates']['arrival_to_osp_receiver']) ?
                    Carbon::parse($data['order_dates']['arrival_to_osp_receiver']) :
                    null,
            ]
        );
    }
}
