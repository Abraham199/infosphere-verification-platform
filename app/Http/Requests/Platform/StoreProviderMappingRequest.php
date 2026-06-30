<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderMappingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole('Super Admin') ?? false; }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'provider' => ['required', 'string', 'max:100'],
            'provider_product_code' => ['required', 'string', 'max:150'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'status' => ['nullable', Rule::in(['active', 'disabled'])],
        ];
    }
}
