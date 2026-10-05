<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isCustomer();
    }

    public function rules(): array
    {
        return [
            'service_type' => ['prohibited'],
            'delivery_method' => ['prohibited'],
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'estimated_weight' => ['nullable', 'numeric', 'min:0.5', 'max:500'],
            'pickup_address_id' => [
                'required',
                Rule::exists('customer_addresses', 'id')->where(
                    fn ($query) => $query->where('user_id', $this->user()->id)
                ),
            ],
            'notes' => ['nullable', 'string', 'max:500'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'pickup_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_type.prohibited' => 'Metode drop-off tidak lagi didukung.',
            'delivery_method.prohibited' => 'Seluruh order wajib menggunakan delivery courier.',
            'service_id.required' => 'Pilih paket layanan laundry.',
            'service_id.exists' => 'Paket layanan laundry tidak valid atau sedang tidak aktif.',
            'pickup_address_id.required' => 'Alamat penjemputan wajib dipilih.',
            'pickup_address_id.exists' => 'Alamat penjemputan tidak valid atau bukan milik Anda.',
            'estimated_weight.min' => 'Perkiraan berat minimal adalah 0.5 kg.',
        ];
    }
}
