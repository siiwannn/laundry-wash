<?php

namespace App\Http\Requests\Order;

use App\Enums\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isCustomer();
    }

    public function rules(): array
    {
        return [
            'service_type' => ['required', new Enum(ServiceType::class)],
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'estimated_weight' => ['nullable', 'numeric', 'min:0.5', 'max:500'],
            'pickup_address_id' => [
                'required_if:service_type,pickup_and_delivery',
                'nullable',
                Rule::exists('customer_addresses', 'id')->where(
                    fn ($query) => $query->where('user_id', $this->user()->id)
                ),
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_type.required' => 'Pilih jenis layanan antar/jemput.',
            'service_id.required' => 'Pilih paket layanan laundry.',
            'service_id.exists' => 'Paket layanan laundry tidak valid atau sedang tidak aktif.',
            'pickup_address_id.required_if' => 'Alamat penjemputan wajib dipilih untuk layanan Antar Jemput.',
            'pickup_address_id.exists' => 'Alamat penjemputan tidak valid atau bukan milik Anda.',
            'estimated_weight.min' => 'Perkiraan berat minimal adalah 0.5 kg.',
        ];
    }
}
