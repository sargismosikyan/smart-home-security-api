<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id'   => ['required', 'integer', 'exists:devices,id'],
            'event_type'  => ['required', 'string', 'max:255'],
            'payload'     => ['sometimes', 'nullable', 'array'],
            'occurred_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
