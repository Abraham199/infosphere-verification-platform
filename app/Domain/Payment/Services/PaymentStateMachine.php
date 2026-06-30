<?php

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\InvalidPaymentStateException;
use App\Domain\Payment\Models\PaymentTransaction;

class PaymentStateMachine
{
    private const ALLOWED = [
        'initialized' => ['pending', 'success', 'failed', 'cancelled', 'abandoned'],
        'pending' => ['success', 'failed', 'cancelled', 'abandoned'],
        'success' => ['reversed'],
        'failed' => [],
        'cancelled' => [],
        'abandoned' => [],
        'reversed' => [],
    ];

    public function transition(PaymentTransaction $payment, PaymentStatus $to, ?string $failureReason = null): PaymentTransaction
    {
        $from = $payment->status instanceof PaymentStatus ? $payment->status->value : (string) $payment->status;

        if ($from === $to->value) {
            return $payment;
        }

        if (! in_array($to->value, self::ALLOWED[$from] ?? [], true)) {
            throw new InvalidPaymentStateException("Invalid payment transition from {$from} to {$to->value}.");
        }

        $changes = ['status' => $to];

        if ($to === PaymentStatus::SUCCESS) {
            $changes['verified_at'] = now();
            $changes['paid_at'] = $payment->paid_at ?? now();
        }

        if ($to === PaymentStatus::FAILED) {
            $changes['failed_at'] = now();
            $changes['failure_reason'] = $failureReason;
        }

        $payment->forceFill($changes)->save();

        return $payment->refresh();
    }
}
