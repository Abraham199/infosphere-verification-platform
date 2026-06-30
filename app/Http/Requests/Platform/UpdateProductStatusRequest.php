<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductStatusRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->hasRole('Super Admin') ?? false; }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['active', 'disabled'])],
        ];
    }
}
