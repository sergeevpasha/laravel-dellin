<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use SergeevPasha\Dellin\Enum\PaymentType;
use SergeevPasha\Dellin\Enum\DeliveryType;
use SergeevPasha\Dellin\Enum\ShippingType;
use SergeevPasha\Dellin\Enum\RequesterRole;

class EnumController
{
    /**
     * Get all Delivery Types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deliveryTypes(): JsonResponse
    {
        return response()->json(DeliveryType::toArray());
    }

    /**
     * Get all Payment Types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentTypes(): JsonResponse
    {
        return response()->json(PaymentType::toArray());
    }

    /**
     * Get all Requester Roles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function requesterRoles(): JsonResponse
    {
        return response()->json(RequesterRole::toArray());
    }

    /**
     * Get all Shipping Types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function shippingTypes(): JsonResponse
    {
        return response()->json(ShippingType::toArray());
    }
}
