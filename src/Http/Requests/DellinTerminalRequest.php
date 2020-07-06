<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DellinTerminalRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'arrival' => ['required', 'boolean'],
        ];
    }
}
