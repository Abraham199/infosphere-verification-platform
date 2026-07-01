<?php

namespace App\Http\Requests\Platform;

use App\Domain\Support\Enums\AssignmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AssignSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Super Admin') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'assigned_team' => ['nullable', 'string', 'max:100', 'required_without:assigned_to_user_id'],
            'assigned_to_user_id' => ['nullable', 'uuid', 'exists:users,id', 'required_without:assigned_team'],
            'assignment_type' => ['required', new Enum(AssignmentType::class)],
        ];
    }
}
