<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\Service;

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
            'items' => ['prohibited'],
            'service_ids' => ['prohibited'],
            'estimated_weight' => ['prohibited'],
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'estimated_quantity' => ['required', 'numeric', 'min:0.01', 'max:500'],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $service = Service::query()->whereKey($this->input('service_id'))->first();
            $quantity = $this->input('estimated_quantity');

            if (! $service || $quantity === null) {
                return;
            }

            if ($service->unit === 'pcs' && abs((float) $quantity - round((float) $quantity)) > 0.0000001) {
                $validator->errors()->add('estimated_quantity', 'Jumlah barang harus berupa bilangan bulat.');
            }

            if ($service->unit === 'kg' && (float) $quantity < 0.5) {
                $validator->errors()->add('estimated_quantity', 'Perkiraan berat minimal adalah 0.5 kg.');
            }
        });
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
            'estimated_quantity.required' => 'Perkiraan jumlah atau berat wajib diisi.',
        ];
    }
}
