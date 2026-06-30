<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'service_code' => ['required', 'string', Rule::exists('verification_services', 'service_code')->where('status', 'active')],
            'subject_identifier' => ['required', 'string', 'min:3', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:120'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'customer_email' => ['nullable', 'email:rfc', 'max:160'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function payload(): array
    {
        return collect($this->validated())
            ->except(['service_code', 'subject_identifier'])
            ->filter(fn ($value): bool => $value !== null && $value !== '')
            ->put('subject_identifier', $this->validated('subject_identifier'))
            ->all();
    }
}
