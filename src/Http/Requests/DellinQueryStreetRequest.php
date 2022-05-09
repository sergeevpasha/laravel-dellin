<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DellinQueryStreetRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'query' => ['required', 'string'],
        ];
    }
}
