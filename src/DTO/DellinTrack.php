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
        if (is_array($data)) {
            $data = reset($data);
        }
        $price       = 0;
        $derivalDate = null;
        $arrivalDate = null;
        $orderId  = $data['orderId'] ?? null;

        if (isset($data['documents'])) {
            $index = array_search('shipping', array_column($data['documents'], 'documentType'));
            if ($index !== false) {
                $price = $data['documents'][$index]['totalSum'] ?? 0;
            }
        }

        $link = $orderId ? 'https://www.dellin.ru/tracker/orders/' . $orderId . '/' : '';

        if (isset($data['orderedAt'])) {
            $derivalDate = Carbon::parse($data['orderedAt']);
        } elseif (isset($data['orderDates']['derrivalFromOspSender'])) {
            $derivalDate = Carbon::parse($data['orderDates']['derrivalFromOspSender']);
        }

        if (isset($data['arrivalDate'])) {
            $arrivalDate = Carbon::parse($data['arrivalDate']);
        } elseif (isset($data['orderDates']['arrivalToOspReceiver'])) {
            $arrivalDate = Carbon::parse($data['orderDates']['arrivalToOspReceiver']);
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
