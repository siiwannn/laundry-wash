<?php

namespace App\Http\Requests\Courier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isCourier();
    }

    public function rules(): array
    {
        return [
            'assignment_id' => ['required', 'exists:courier_assignments,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'assignment_id.required' => 'ID penugasan wajib dikirim.',
            'assignment_id.exists' => 'Penugasan kurir tidak ditemukan.',
            'latitude.required' => 'Latitude wajib dikirim.',
            'latitude.between' => 'Latitude berada di luar rentang yang valid.',
            'longitude.required' => 'Longitude wajib dikirim.',
            'longitude.between' => 'Longitude berada di luar rentang yang valid.',
            'accuracy.max' => 'Akurasi GPS berada di luar batas yang dapat diterima.',
        ];
    }
}
