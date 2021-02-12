<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\DTO;

use SergeevPasha\Dellin\Enum\DeliveryType;
use Spatie\DataTransferObject\DataTransferObject;
use SergeevPasha\Dellin\DTO\Collection\AcDocCollection;
use SergeevPasha\Dellin\DTO\Collection\PackageCollection;

class Delivery extends DataTransferObject
{
    /**
     * @var \SergeevPasha\Dellin\Enum\DeliveryType
     */
    public DeliveryType $deliveryType;
    
    /**
     * @var \SergeevPasha\Dellin\DTO\ArrivalData
     */
    public ArrivalData $arrival;

    /**
     * @var \SergeevPasha\Dellin\DTO\DerivalData
     */
    public DerivalData $derival;

    /**
     * @var \SergeevPasha\Dellin\DTO\Collection\PackageCollection|null
     */
    public ?PackageCollection $packages;

    /**
     * @var \SergeevPasha\Dellin\DTO\Collection\AcDocCollection|null
     */
    public ?AcDocCollection $acDocs;

    /**
     * @var \SergeevPasha\Dellin\DTO\Requester
     */
    public Requester $requester;

    /**
     * @var \SergeevPasha\Dellin\DTO\Cargo
     */
    public Cargo $cargo;

    /**
     * @var \SergeevPasha\Dellin\DTO\Payment
     */
    public Payment $payment;
    
    /**
     * @var string|null
     */
    public ?string $session;

    /**
     * From Array.
     *
     * @param array<mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $acDocs = [];
        if (isset($data['ac_docs_send'])) {
            $acDocs[] = AcDoc::fromValue(0);
        }
        if (isset($data['ac_docs_return'])) {
            $acDocs[] = AcDoc::fromValue(1);
        }
        $acDocsCollection = !empty($acDocs) ? AcDocCollection::create($acDocs) : null;
        return new self([
            'deliveryType' => DeliveryType::fromValue((int) $data['delivery_type']),
            'arrival'      => ArrivalData::fromArray($data),
            'derival'      => DerivalData::fromArray($data),
            'packages'     => isset($data['packages']) ? PackageCollection::fromArray($data) : null,
            'acDocs'       => $acDocsCollection,
            'requester'    => Requester::fromArray((int) $data['requester_role'], $data['requester_uid']),
            'cargo'        => Cargo::fromArray($data),
            'payment'      => Payment::fromArray($data),
            'session'      => isset($data['session_id']) ? $data['session_id'] : null,
        ]);
    }
}
