<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOwnProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true || $this->user()?->isCourier() === true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users')->ignore($this->user())],
            'phone' => ['required', 'string', 'max:20'],
        ];

        if ($this->user()->isCourier()) {
            $rules['vehicle_type'] = ['required', 'string', 'max:50'];
            $rules['vehicle_plate'] = ['required', 'string', 'max:20'];
        } else {
            $rules['vehicle_type'] = ['prohibited'];
            $rules['vehicle_plate'] = ['prohibited'];
        }

        return $rules;
    }
}
