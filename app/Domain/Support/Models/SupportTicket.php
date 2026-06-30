<?php

namespace App\Domain\Support\Models;

use App\Domain\Support\Enums\TicketStatus;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'requester_id', 'support_category_id', 'support_priority_id', 'support_sla_policy_id', 'ticket_reference', 'subject', 'description', 'status', 'source', 'first_response_due_at', 'resolution_due_at', 'escalation_due_at', 'first_responded_at', 'resolved_at', 'closed_at', 'sla_breached_at', 'metadata'];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'first_response_due_at' => 'datetime',
            'resolution_due_at' => 'datetime',
            'escalation_due_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'sla_breached_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'support_category_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(SupportPriority::class, 'support_priority_id');
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SupportSlaPolicy::class, 'support_sla_policy_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SupportAssignment::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(SupportTicketNote::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SupportTicketAttachment::class);
    }
}
