<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Libraries;

use Illuminate\Support\Str;
use SergeevPasha\Dellin\DTO\Cargo;
use SergeevPasha\Dellin\DTO\Payment;
use SergeevPasha\Dellin\DTO\Requester;
use SergeevPasha\Dellin\DTO\ArrivalData;
use SergeevPasha\Dellin\DTO\DerivalData;
use SergeevPasha\Dellin\Enum\DeliveryType;
use SergeevPasha\Dellin\DTO\Collection\AcDocCollection;
use SergeevPasha\Dellin\DTO\Collection\PackageCollection;

class RequestBuilder
{
    /**
     * Build Delivery Type request.
     *
     * @param  \SergeevPasha\Dellin\Enum\DeliveryType $deliveryType
     * @return array
     */
    public function buildDeliveryType(DeliveryType $deliveryType): array
    {
        return [
            'type' => Str::lower($deliveryType->key),
        ];
    }

    /**
     * Build Arrival request.
     *
     * @param \SergeevPasha\Dellin\DTO\ArrivalData $arrival
     * @return array
     */
    public function buildArrival(ArrivalData $arrival): array
    {
        return $this->buildArrivalDerival($arrival);
    }

    /**
     * Build Derival request.
     *
     * @param \SergeevPasha\Dellin\DTO\DerivalData $derival
     * @return array
     */
    public function buildDerival(DerivalData $derival): array
    {
        $request = $this->buildArrivalDerival($derival);
        $request['produceDate'] = $derival->produceDate;
        return $request;
    }

    /**
     * Build Arrival Derival common data.
     *
     * @param \SergeevPasha\Dellin\DTO\ArrivalData|\SergeevPasha\Dellin\DTO\DerivalData $arrivalDerival
     * @return array
     */
    private function buildArrivalDerival($arrivalDerival): array
    {
        $request = [];
        $request['variant'] = Str::lower($arrivalDerival->variant->key);
        /* Only one of this parameters is allowed */
        if ($arrivalDerival->terminalID) {
            $request['terminalID'] = $arrivalDerival->terminalID;
        } elseif ($arrivalDerival->addressID) {
            $request['addressID'] = $arrivalDerival->addressID;
        } elseif ($arrivalDerival->address['street']) {
            $request['address']['street'] = $arrivalDerival->address['street'];
        } elseif ($arrivalDerival->city) {
            $request['city'] = $arrivalDerival->city;
        }
        if (Str::lower($arrivalDerival->variant->key) === 'address') {
            $request['time'] = $this->buildTime($arrivalDerival);
        }
        if ($arrivalDerival->handling['freightLift']) {
            $request['handling']['freightLift'] = $arrivalDerival->handling['freightLift'];
        }
        if ($arrivalDerival->handling['toFloor']) {
            $request['handling']['toFloor'] = $arrivalDerival->handling['toFloor'];
        }
        if ($arrivalDerival->handling['carry']) {
            $request['handling']['carry'] = $arrivalDerival->handling['carry'];
        }
        if ($arrivalDerival->requirements) {
            $request['requirements'] = $arrivalDerival->requirements;
        }
        return $request;
    }

    /**
     * Build Arrival Derival Time.
     *
     * @param \SergeevPasha\Dellin\DTO\ArrivalData|\SergeevPasha\Dellin\DTO\DerivalData $arrivalDerival
     * @return array
     */
    private function buildTime($arrivalDerival): array
    {
        $request = [];
        /*  Time can be specified only on Address delivery type */
        if ($arrivalDerival->time['worktimeStart']) {
            $request['worktimeStart'] = $arrivalDerival->time['worktimeStart'];
        }
        if ($arrivalDerival->time['worktimeEnd']) {
            $request['worktimeEnd'] = $arrivalDerival->time['worktimeEnd'];
        }
        if ($arrivalDerival->time['breakStart']) {
            $request['breakStart'] = $arrivalDerival->time['breakStart'];
        }
        if ($arrivalDerival->time['breakEnd']) {
            $request['breakEnd'] = $arrivalDerival->time['breakEnd'];
        }
        if ($arrivalDerival->time['exactTime']) {
            $request['exactTime'] = $arrivalDerival->time['exactTime'];
        }
        return $request;
    }

    /**
     * Build Packages request.
     *
     * @param \SergeevPasha\Dellin\DTO\Collection\PackageCollection $packages
     * @return array
     */
    public function buildPackages(PackageCollection $packages): array
    {
        $request = [];
        foreach ($packages as $package) {
            $array = [];
            $array['uid'] = $package->uid;
            if ($package->count) {
                $array['count'] = $package->count;
            }
            $request[] = $array;
        }
        return $request;
    }

    /**
     * Build AcDocs request.
     *
     * @param \SergeevPasha\Dellin\DTO\Collection\AcDocCollection $acDocs
     * @return array
     */
    public function buildAcDocs(AcDocCollection $acDocs): array
    {
        $request = [];
        foreach ($acDocs as $doc) {
            $request[]['action'] = Str::lower($doc->action->key);
        }
        return $request;
    }

    /**
     * Build Requesters request.
     *
     * @param \SergeevPasha\Dellin\DTO\Requester $requester
     * @return array
     */
    public function buildRequester(Requester $requester): array
    {
        return [
            'role' => Str::lower($requester->role->key),
            'uid'  => $requester->uid,
        ];
    }

    /**
     * Build Cargo request.
     *
     * @param \SergeevPasha\Dellin\DTO\Cargo $cargo
     * @return array
     */
    public function buildCargo(Cargo $cargo): array
    {
        $request = [
            'quantity'    => $cargo->quantity,
            'length'      => $cargo->length,
            'width'       => $cargo->width,
            'weight'      => $cargo->weight,
            'height'      => $cargo->height,
            'totalVolume' => $cargo->totalVolume,
            'totalWeight' => $cargo->totalWeight,
            'hazardClass' => $cargo->hazardClass,
        ];
        if ($cargo->freightUID) {
            $request['freightUID'] = $cargo->freightUID;
        } elseif ($cargo->freightName) {
            $request['freightName'] = $cargo->freightName;
        }
        if ($cargo->oversizedWeight) {
            $request['oversizedWeight'] = $cargo->oversizedWeight;
        }
        if ($cargo->oversizedVolume) {
            $request['oversizedVolume'] = $cargo->oversizedVolume;
        }
        if ($cargo->insurance) {
            $request['insurance']['statedValue'] = $cargo->insurance->statedValue;
            $request['insurance']['term'] = $cargo->insurance->term;
        }
        return $request;
    }

    /**
     * Build Payment request.
     *
     * @param \SergeevPasha\Dellin\DTO\Payment $payment
     * @return array
     */
    public function buildPayment(Payment $payment): array
    {
        return [
            'paymentCity' => $payment->paymentCity,
            'type'        => Str::lower($payment->type->key),
        ];
    }
}
