<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InitializeWalletFundingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:100', 'max:5000000'],
            'currency' => ['required', Rule::in(['NGN'])],
            'email' => ['required', 'email:rfc'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Wallet funding amount must be at least NGN 100.00.',
            'currency.in' => 'Only NGN wallet funding is currently supported.',
        ];
    }
}
