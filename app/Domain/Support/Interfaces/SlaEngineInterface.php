<?php

namespace App\Domain\Support\Interfaces;

use App\Domain\Support\DTOs\SlaPolicyData;
use App\Domain\Support\Models\SupportSlaPolicy;
use App\Domain\Support\Models\SupportTicket;

interface SlaEngineInterface
{
    public function createPolicy(SlaPolicyData $data): SupportSlaPolicy;
    public function applyToTicket(SupportTicket $ticket): SupportTicket;
    public function isBreached(SupportTicket $ticket): bool;
}
