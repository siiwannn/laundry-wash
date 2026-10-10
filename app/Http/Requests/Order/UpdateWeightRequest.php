<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateWeightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'actual_quantity' => ['required', 'numeric', 'min:0.01', 'max:500'],
            'actual_weight' => ['prohibited'],
            'additional_fee' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $order = $this->route('order');
            $item = is_object($order) ? $order->serviceItem()->first() : null;
            $quantity = $this->input('actual_quantity');

            if (! $item || $quantity === null) {
                return;
            }

            if ($item->unit === 'pcs' && abs((float) $quantity - round((float) $quantity)) > 0.0000001) {
                $validator->errors()->add('actual_quantity', 'Jumlah aktual barang harus berupa bilangan bulat.');
            }

            if ($item->unit === 'kg' && (float) $quantity < 0.1) {
                $validator->errors()->add('actual_quantity', 'Berat aktual minimal 0.1 kg.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'actual_quantity.required' => 'Kuantitas aktual wajib diisi.',
            'actual_quantity.min' => 'Kuantitas aktual tidak valid.',
            'additional_fee.min' => 'Biaya tambahan tidak boleh bernilai negatif.',
        ];
    }
}
