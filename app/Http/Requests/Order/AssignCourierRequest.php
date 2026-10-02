<?php

namespace App\Http\Requests\Order;

use App\Enums\AssignmentType;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class AssignCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'courier_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', UserRole::COURIER->value)
                        ->where('is_active', true);
                }),
            ],
            'type' => ['required', new Enum(AssignmentType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'courier_id.required' => 'Pilih kurir yang akan ditugaskan.',
            'courier_id.exists' => 'Kurir yang dipilih tidak ditemukan atau sedang tidak aktif.',
            'type.required' => 'Tipe penugasan wajib ditentukan.',
        ];
    }
}
