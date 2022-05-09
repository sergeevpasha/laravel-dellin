<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use Carbon\Carbon;
use Spatie\DataTransferObject\DataTransferObject;

class DellinTrack extends DataTransferObject
{
    /**
     * @var string|null
     */
    public ?string $status;

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
        $price       = 0;
        $derivalDate = null;
        $arrivalDate = null;

        if (isset($data['documents'])) {
            $index = array_search('shipping', array_column($data['documents'], 'document_type'));
            if ($index !== false) {
                $price = $data['documents'][$index]['total_sum'] ?? 0;
            }
        }

        if (isset($data['ordered_at'])) {
            $derivalDate = Carbon::parse($data['ordered_at']);
        } elseif (isset($data['order_dates']['derrival_from_osp_sender'])) {
            $derivalDate = Carbon::parse($data['order_dates']['derrival_from_osp_sender']);
        }

        if (isset($data['arrival_date'])) {
            $arrivalDate = Carbon::parse($data['arrival_date']);
        } elseif (isset($data['order_dates']['arrival_to_osp_receiver'])) {
            $arrivalDate = Carbon::parse($data['order_dates']['arrival_to_osp_receiver']);
        }

        return new self(
            [
                'status'      => $data['state_name'] ?? null,
                'price'       => (float) $price,
                'startDate'   => $derivalDate,
                'receiveDate' => $arrivalDate,
            ]
        );
    }
}
