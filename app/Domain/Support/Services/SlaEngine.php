<?php

namespace App\Domain\Support\Services;

use App\Domain\Support\DTOs\SlaPolicyData;
use App\Domain\Support\Events\SLAExceeded;
use App\Domain\Support\Interfaces\SlaEngineInterface;
use App\Domain\Support\Interfaces\SupportRepositoryInterface;
use App\Domain\Support\Models\SupportSlaPolicy;
use App\Domain\Support\Models\SupportTicket;
use App\Domain\Support\Validators\SupportValidationService;

class SlaEngine implements SlaEngineInterface
{
    public function __construct(
        private readonly SupportRepositoryInterface $support,
        private readonly SupportValidationService $validator,
    ) {
    }

    public function createPolicy(SlaPolicyData $data): SupportSlaPolicy
    {
        $this->validator->validateSlaPolicy($data);

        return $this->support->createSlaPolicy([
            'tenant_id' => $data->tenantId,
            'support_priority_id' => $data->priorityId,
            'name' => $data->name,
            'first_response_minutes' => $data->firstResponseMinutes,
            'resolution_minutes' => $data->resolutionMinutes,
            'escalation_minutes' => $data->escalationMinutes,
            'business_hours' => $data->businessHours,
            'metadata' => $data->metadata,
            'is_active' => true,
        ]);
    }

    public function applyToTicket(SupportTicket $ticket): SupportTicket
    {
        $policy = $this->support->activeSlaPolicy($ticket->tenant_id, $ticket->support_priority_id);

        if ($policy === null) {
            return $ticket;
        }

        $ticket->forceFill([
            'support_sla_policy_id' => $policy->id,
            'first_response_due_at' => $ticket->created_at?->copy()->addMinutes($policy->first_response_minutes) ?? now()->addMinutes($policy->first_response_minutes),
            'resolution_due_at' => $ticket->created_at?->copy()->addMinutes($policy->resolution_minutes) ?? now()->addMinutes($policy->resolution_minutes),
            'escalation_due_at' => $policy->escalation_minutes === null ? null : ($ticket->created_at?->copy()->addMinutes($policy->escalation_minutes) ?? now()->addMinutes($policy->escalation_minutes)),
        ])->save();

        return $ticket;
    }

    public function isBreached(SupportTicket $ticket): bool
    {
        $breached = ($ticket->first_response_due_at !== null && $ticket->first_responded_at === null && now()->greaterThan($ticket->first_response_due_at))
            || ($ticket->resolution_due_at !== null && $ticket->resolved_at === null && now()->greaterThan($ticket->resolution_due_at));

        if ($breached && $ticket->sla_breached_at === null) {
            $ticket->forceFill(['sla_breached_at' => now()])->save();
            event(new SLAExceeded($ticket));
        }

        return $breached;
    }
}
