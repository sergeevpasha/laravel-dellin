<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use Carbon\Carbon;
use SergeevPasha\Dellin\Enum\DeliveryType;
use Spatie\DataTransferObject\DataTransferObject;

class DellinTrack extends DataTransferObject
{
    public const STATE_DECLINED = 'declined';

    /**
     * @var string|null
     */
    public ?string $status;

    /**
     * Order state code, e.g. "inway", "arrived", "finished", "declined" (v1/references/statuses).
     *
     * @var string|null
     */
    public ?string $state;

    /**
     * @var string|null
     */
    public ?string $declineReason;

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
     * @var \Carbon\Carbon|null
     */
    public ?Carbon $warehousing;

    /**
     * @var int|null
     */
    public ?int $derivalTerminalId;

    /**
     * @var string|null
     */
    public ?string $derivalTerminalAddress;

    /**
     * @var string|null
     */
    public ?string $derivalAddress;

    /**
     * @var int|null
     */
    public ?int $arrivalTerminalId;

    /**
     * @var string|null
     */
    public ?string $arrivalTerminalAddress;

    /**
     * @var string|null
     */
    public ?string $arrivalAddress;

    /**
     * @var string|null
     */
    public ?string $deliveryType;

    /**
     * @var int|null
     */
    public ?int $deliveryDays;

    /**
     * @var string|null
     */
    public ?string $derivalCity;

    /**
     * City of the serving terminal in Dellin's terminal directory (v3/public/terminals city "id", 1–311),
     * not the settlement's cityID of v2/public/kladr. For a door delivery it is another city:
     * Donetsk is served from Mariupol.
     *
     * @var int|null
     */
    public ?int $derivalCityId;

    /**
     * Settlement code (25 digits): findCityByCode() turns it into the cityID of v2/public/kladr.
     *
     * @var string|null
     */
    public ?string $derivalCityCode;

    /**
     * @var string|null
     */
    public ?string $arrivalCity;

    /**
     * Same as $derivalCityId: the serving terminal's city, not the settlement.
     *
     * @var int|null
     */
    public ?int $arrivalCityId;

    /**
     * @var string|null
     */
    public ?string $arrivalCityCode;

    /**
     * @var string|null
     */
    public ?string $derivalTerminalName;

    /**
     * @var string|null
     */
    public ?string $arrivalTerminalName;

    /**
     * @var \Carbon\Carbon|null
     */
    public ?Carbon $actualReceiveDate;

    /**
     * @var \Carbon\Carbon|null
     */
    public ?Carbon $produceDate;

    /**
     * @var array|null
     */
    public ?array $additionalServices;

    /**
     * @var bool|null
     */
    public ?bool $isPaid;

    /**
     * @var float|null
     */
    public ?float $statedValue;

    /**
     * @var bool|null
     */
    public ?bool $withWebOrder;

    /**
     * @var float|null
     */
    public ?float $webOrderItemsSum;

    /**
     * @var bool
     */
    public bool $derivalIsTerminal;

    /**
     * @var bool
     */
    public bool $arrivalIsTerminal;

    /**
     * Dellin does not know the order (yet): error 717001 or no orders in the answer.
     *
     * @var bool
     */
    public bool $notFound = false;

    /**
     * From Array.
     *
     * @param array $data
     *
     * @return self
     * @throws \Spatie\DataTransferObject\Exceptions\UnknownProperties
     */
    public static function fromArray(array $data): self
    {
        $notFound = !isset($data['orders'][0]);
        $data = $data['orders'][0] ?? [];
        $derivalDate = null;
        $arrivalDate = null;
        $warehousing = null;
        $orderId = $data['orderId'] ?? null;
        $price = $data['totalSum'] ?? 0;
        $derivalTerminalId = $data['derival']['terminalId'] ?? null;
        $derivalTerminalAddress = $data['derival']['terminalAddress'] ?? null;
        $derivalAddress = $data['derival']['address'] ?? null;
        $derivalCity = $data['derival']['city'] ?? null;
        $derivalCityId = isset($data['derival']['cityId']) ? (int) $data['derival']['cityId'] : null;
        $derivalCityCode = isset($data['derival']['cityCode']) ? (string) $data['derival']['cityCode'] : null;
        $derivalTerminalName = $data['derival']['terminalName'] ?? null;
        $arrivalTerminalAddress = $data['arrival']['terminalAddress'] ?? null;
        $arrivalTerminalId = $data['arrival']['terminalId'] ?? null;
        $arrivalAddress = $data['arrival']['address'] ?? null;
        $arrivalCity = $data['arrival']['city'] ?? null;
        $arrivalCityId = isset($data['arrival']['cityId']) ? (int) $data['arrival']['cityId'] : null;
        $arrivalCityCode = isset($data['arrival']['cityCode']) ? (string) $data['arrival']['cityCode'] : null;
        $arrivalTerminalName = $data['arrival']['terminalName'] ?? null;

        $deliveryDays = isset($data['orderTimeInDays']['delivery']) ? (int) $data['orderTimeInDays']['delivery'] : null;

        $deliveryType = null;
        $additionalServices = null;
        $docs = $data['documents'] ?? [];
        foreach ($docs as $doc) {
            if (($doc['type'] ?? '') === 'shipping') {
                if (!empty($doc['serviceKind']) && !$deliveryType) {
                    $deliveryType = DeliveryType::fromServiceKind($doc['serviceKind'])?->key;
                }
                if (!empty($doc['services'])) {
                    $additionalServices = $doc['services'];
                }
            }
        }

        $derivalIsTerminal = !($data['orderedDeliveryFromAddress'] ?? false);
        $arrivalIsTerminal = !($data['orderedDeliveryToAddress'] ?? false);

        $link = $orderId ? 'https://www.dellin.ru/tracker/orders/' . $orderId . '/' : '';

        if (isset($data['orderDates']['derivalFromOspSender'])) {
            $derivalDate = Carbon::parse($data['orderDates']['derivalFromOspSender']);
        } elseif (isset($data['orderDates']['arrivalToOspReceiver'])) {
            $derivalDate = Carbon::parse($data['orderDates']['arrivalToOspReceiver']);
        }

        if (isset($data['orderDates']['giveoutFromOspReceiver'])) {
            $arrivalDate = Carbon::parse($data['orderDates']['giveoutFromOspReceiver']);
        } elseif (isset($data['orderDates']['arrivalToOspReceiver'])) {
            $arrivalDate = Carbon::parse($data['orderDates']['arrivalToOspReceiver']);
        }

        $actualReceiveDate = null;
        if (isset($data['orderDates']['arrivalToReceiver'])) {
            $actualReceiveDate = Carbon::parse($data['orderDates']['arrivalToReceiver']);
        }

        $produceDate = null;
        if (isset($data['produceDate'])) {
            $produceDate = Carbon::parse($data['produceDate']);
        }

        if (isset($data['orderDates']['warehousing'])) {
            $warehousing = Carbon::parse($data['orderDates']['warehousing']);
        }

        return new self(
            [
                'status'                 => $data['stateName'] ?? null,
                'state'                  => $data['state'] ?? null,
                'declineReason'          => is_string($data['declineReason'] ?? null) ? $data['declineReason'] : null,
                'price'                  => (float)$price,
                'link'                   => $link,
                'startDate'              => $derivalDate,
                'receiveDate'            => $arrivalDate,
                'actualReceiveDate'      => $actualReceiveDate,
                'produceDate'            => $produceDate,
                'warehousing'            => $warehousing,
                'derivalTerminalId'      => $derivalTerminalId,
                'derivalTerminalAddress' => $derivalTerminalAddress,
                'derivalAddress'         => $derivalAddress,
                'derivalCity'            => $derivalCity,
                'derivalCityId'          => $derivalCityId,
                'derivalCityCode'        => $derivalCityCode,
                'derivalTerminalName'    => $derivalTerminalName,
                'arrivalTerminalId'      => $arrivalTerminalId,
                'arrivalTerminalAddress' => $arrivalTerminalAddress,
                'arrivalAddress'         => $arrivalAddress,
                'arrivalCity'            => $arrivalCity,
                'arrivalCityId'          => $arrivalCityId,
                'arrivalCityCode'        => $arrivalCityCode,
                'arrivalTerminalName'    => $arrivalTerminalName,
                'derivalIsTerminal'      => $derivalIsTerminal,
                'arrivalIsTerminal'      => $arrivalIsTerminal,
                'deliveryType'           => $deliveryType,
                'deliveryDays'           => $deliveryDays,
                'additionalServices'     => $additionalServices,
                'isPaid'                 => $data['isPaid'] ?? null,
                'statedValue'            => $data['statedValue'] ?? null,
                'withWebOrder'           => $data['withWebOrder'] ?? null,
                'webOrderItemsSum'       => $data['webOrderItemsSum'] ?? null,
                'notFound'               => $notFound,
            ]
        );
    }

    public function isDeclined(): bool
    {
        return $this->state === self::STATE_DECLINED;
    }
}
