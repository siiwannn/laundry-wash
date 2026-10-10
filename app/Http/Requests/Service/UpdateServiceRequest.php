<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('services', 'name')->whereNull('deleted_at')->ignore($this->route('service')?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit' => ['required', 'in:kg,pcs'],
            'price_per_unit' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'estimated_hours' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
