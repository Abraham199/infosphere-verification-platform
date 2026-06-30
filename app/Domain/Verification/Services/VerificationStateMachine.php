<?php

namespace App\Domain\Verification\Services;

use App\Domain\Verification\Enums\VerificationStatus;
use App\Domain\Verification\Exceptions\InvalidVerificationStateException;
use App\Domain\Verification\Models\VerificationRequest;

class VerificationStateMachine
{
    private const ALLOWED = [
        'created' => ['validating', 'cancelled'],
        'validating' => ['submitted', 'failed', 'cancelled'],
        'submitted' => ['processing', 'completed', 'failed'],
        'processing' => ['completed', 'failed'],
        'completed' => ['refunded'],
        'failed' => ['refunded'],
        'cancelled' => [],
        'refunded' => [],
    ];

    public function transition(VerificationRequest $request, VerificationStatus $to, ?string $reason = null): VerificationRequest
    {
        $from = $request->status instanceof VerificationStatus ? $request->status->value : (string) $request->status;

        if ($from === $to->value) {
            return $request;
        }

        if (! in_array($to->value, self::ALLOWED[$from] ?? [], true)) {
            throw new InvalidVerificationStateException("Invalid verification transition from {$from} to {$to->value}.");
        }

        $changes = ['status' => $to];

        if ($to === VerificationStatus::SUBMITTED) {
            $changes['submitted_at'] = now();
        }

        if ($to === VerificationStatus::COMPLETED) {
            $changes['completed_at'] = now();
        }

        if ($to === VerificationStatus::FAILED) {
            $changes['failed_at'] = now();
            $changes['failure_reason'] = $reason;
        }

        $request->forceFill($changes)->save();

        return $request->refresh();
    }
}
