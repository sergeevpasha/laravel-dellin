<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DellinCounterpartiesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'session_id' => ['sometimes', 'required', 'string'],
            'expand' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
