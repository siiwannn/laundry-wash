<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'pickup_fee' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'delivery_fee' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ];
    }
}
