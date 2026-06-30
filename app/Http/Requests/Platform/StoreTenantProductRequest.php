<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole('Super Admin') ?? false; }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'uuid', 'exists:tenants,id'],
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'is_enabled' => ['nullable', 'boolean'],
            'selling_price_override' => ['nullable', 'numeric', 'min:0'],
            'status_override' => ['nullable', Rule::in(['draft', 'active', 'disabled', 'maintenance', 'deprecated', 'archived'])],
            'visibility' => ['nullable', Rule::in(['private', 'public', 'tenant_only'])],
        ];
    }
}
