<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Http\Requests;

use BenSampo\Enum\Rules\EnumValue;
use SergeevPasha\Dellin\Enum\PaymentType;
use SergeevPasha\Dellin\Enum\DeliveryType;
use SergeevPasha\Dellin\Enum\ShippingType;
use Illuminate\Foundation\Http\FormRequest;
use SergeevPasha\Dellin\Enum\RequesterRole;
use SergeevPasha\Dellin\Rules\ValidatePackages;

class DellinCalculatePriceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'session_id'             => ['sometimes', 'required', 'string'],
            'delivery_type'          => ['required', new EnumValue(DeliveryType::class, false)],
            'arrival_shipping_type'  => ['required', new EnumValue(ShippingType::class, false)],
            'arrival_terminal_id'    => ['sometimes', 'required', 'integer'],
            'arrival_address_id'     => ['sometimes', 'required', 'integer'],
            'arrival_street_code'    => ['sometimes', 'required', 'string'],
            'arrival_city_code'      => ['sometimes', 'required', 'string'],
            'arrival_worktime_start' => ['sometimes', 'required', 'date_format:H:i'],
            'arrival_worktime_end'   => ['sometimes', 'required', 'date_format:H:i'],
            'arrival_break_start'    => ['sometimes', 'required', 'date_format:H:i'],
            'arrival_break_end'      => ['sometimes', 'required', 'date_format:H:i'],
            'arrival_exact_time'     => ['sometimes', 'required', 'boolean'],
            'arrival_freight_lift'   => ['sometimes', 'required', 'boolean'],
            'arrival_to_floor'       => ['sometimes', 'required', 'integer'],
            'arrival_carry'          => ['sometimes', 'required', 'integer'],
            'arrival_requirements'   => ['sometimes', 'required', 'array'],
            'derival_produce_date'   => ['required', 'date_format:Y-m-d'],
            'derival_shipping_type'  => ['required', new EnumValue(ShippingType::class, false)],
            'derival_terminal_id'    => ['sometimes', 'required', 'integer'],
            'derival_address_id'     => ['sometimes', 'required', 'integer'],
            'derival_street_code'    => ['sometimes', 'required', 'string'],
            'derival_city_code'      => ['sometimes', 'required', 'string'],
            'derival_worktime_start' => ['sometimes', 'required', 'date_format:H:i'],
            'derival_worktime_end'   => ['sometimes', 'required', 'date_format:H:i'],
            'derival_break_start'    => ['sometimes', 'required', 'date_format:H:i'],
            'derival_break_end'      => ['sometimes', 'required', 'date_format:H:i'],
            'derival_exact_time'     => ['sometimes', 'required', 'boolean'],
            'derival_freight_lift'   => ['sometimes', 'required', 'boolean'],
            'derival_to_floor'       => ['sometimes', 'required', 'integer'],
            'derival_carry'          => ['sometimes', 'required', 'integer'],
            'derival_requirements'   => ['sometimes', 'required', 'array'],
            'packages'               => ['sometimes', 'required', new ValidatePackages()],
            'ac_docs_send'           => ['sometimes', 'required', 'boolean'],
            'ac_docs_return'         => ['sometimes', 'required', 'boolean'],
            'requester_role'         => ['sometimes', new EnumValue(RequesterRole::class, false)],
            'requester_uid'          => ['sometimes', 'string'],
            'cargo_quantity'         => ['sometimes', 'required', 'integer'],
            'cargo_length'           => ['required_unless:delivery_type,2', 'numeric'],
            'cargo_height'           => ['required_unless:delivery_type,2', 'numeric'],
            'cargo_width'            => ['required_unless:delivery_type,2', 'numeric'],
            'cargo_weight'           => ['required_unless:delivery_type,2', 'numeric'],
            'cargo_total_volume'     => ['required_unless:delivery_type,2', 'numeric'],
            'cargo_total_weight'     => ['required_unless:delivery_type,2', 'numeric'],
            'cargo_oversized_weight' => ['sometimes', 'required_unless:delivery_type,2', 'numeric'],
            'cargo_oversized_volume' => ['sometimes', 'required_unless:delivery_type,2', 'numeric'],
            'cargo_freight_uid'      => ['sometimes', 'string'],
            'cargo_freight_name'     => ['sometimes', 'string'],
            'cargo_hazard_class'     => ['sometimes', 'numeric'],
            'insurance_value'        => ['sometimes', 'required', 'numeric'],
            'insurance_term'         => ['required_with:insurance_value', 'boolean'],
            'payment_city'           => ['required', 'string'],
            'payment_type'           => ['required', new EnumValue(PaymentType::class, false)],
        ];
    }
}
