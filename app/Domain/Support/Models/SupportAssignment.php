<?php

namespace App\Domain\Support\Models;

use App\Domain\Support\Enums\AssignmentType;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportAssignment extends Model
{
    use HasUuid;

    protected $fillable = ['support_ticket_id', 'assigned_to_user_id', 'assigned_team', 'assignment_type', 'assigned_by', 'assigned_at', 'released_at', 'metadata'];

    protected function casts(): array
    {
        return [
            'assignment_type' => AssignmentType::class,
            'assigned_at' => 'datetime',
            'released_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
