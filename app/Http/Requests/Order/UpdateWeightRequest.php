<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWeightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'actual_weight' => ['required', 'numeric', 'min:0.1', 'max:500'],
            'additional_fee' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'actual_weight.required' => 'Berat aktual wajib diisi.',
            'actual_weight.min' => 'Berat aktual minimal 0.1 kg.',
            'additional_fee.min' => 'Biaya tambahan tidak boleh bernilai negatif.',
        ];
    }
}
