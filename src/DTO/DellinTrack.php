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
        error_log(print_r($data, true));
        $derivalDate = null;
        $arrivalDate = null;
        $orderId  = $data['orderId'] ?? null;
        $price = $data['totalSum'] ?? 0;


        $link = $orderId ? 'https://www.dellin.ru/tracker/orders/' . $orderId . '/' : '';

        if (isset($data['orderDate'])) {
            $derivalDate = Carbon::parse($data['orderDate']);
        } elseif (isset($data['orderDates']['derrivalFromOspSender'])) {
            $derivalDate = Carbon::parse($data['orderDates']['derrivalFromOspSender']);
        }

        if (isset($data['orderDates']['giveoutFromOspReceiver'])) {
            $derivalDate = Carbon::parse($data['orderDates']['giveoutFromOspReceiver']);
        } elseif (isset($data['orderDates']['arrivalToOspReceiver'])) {
            $arrivalDate = Carbon::parse($data['orderDates']['arrivalToOspReceiver']);
        }

        return new self(
            [
                'status'      => $data['stateName'] ?? null,
                'price'       => (float) $price,
                'link'        => $link,
                'startDate'   => $derivalDate,
                'receiveDate' => $arrivalDate,
            ]
        );
    }
}
