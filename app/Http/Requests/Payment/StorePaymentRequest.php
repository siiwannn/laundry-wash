<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', new Enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:100'],
            'proof_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'method.required' => 'Pilih metode pembayaran.',
            'proof_file.image' => 'File bukti transfer harus berupa gambar.',
            'proof_file.max' => 'Ukuran gambar maksimal adalah 4 MB.',
        ];
    }
}
