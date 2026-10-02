<?php

namespace App\Http\Requests\Courier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCourier() === true;
    }

    public function rules(): array
    {
        return ['status' => ['required', 'in:available,offline']];
    }
}
