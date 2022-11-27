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
     * @var string
     */
    public string $link;

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
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $price       = 0;
        $derivalDate = null;
        $arrivalDate = null;
        $orderId  = $data['order_id'] ?? null;

        if (isset($data['documents'])) {
            $index = array_search('shipping', array_column($data['documents'], 'document_type'));
            if ($index !== false) {
                $price = $data['documents'][$index]['total_sum'] ?? 0;
            }
        }

        $link = $orderId ? 'https://www.dellin.ru/tracker/orders/' . $orderId . '/' : '';

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
                'link'        => $link,
                'startDate'   => $derivalDate,
                'receiveDate' => $arrivalDate,
            ]
        );
    }
}
