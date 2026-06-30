<?php

namespace App\Http\Requests\Platform;

use App\Domain\Product\Enums\ProductCapability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductCapabilitiesRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole('Super Admin') ?? false; }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'capabilities' => ['array'],
            'capabilities.*' => [Rule::in(array_column(ProductCapability::cases(), 'value'))],
        ];
    }
}
