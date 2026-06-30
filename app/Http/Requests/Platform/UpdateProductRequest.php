<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole('Super Admin') ?? false; }

    public function rules(): array
    {
        return [
            'product_category_id' => ['required', 'uuid', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(['NGN'])],
            'visibility' => ['required', Rule::in(['private', 'public', 'tenant_only'])],
        ];
    }
}
