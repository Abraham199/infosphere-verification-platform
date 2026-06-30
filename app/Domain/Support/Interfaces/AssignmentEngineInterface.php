<?php

namespace App\Domain\Support\Interfaces;

use App\Domain\Support\DTOs\AssignmentData;
use App\Domain\Support\Models\SupportAssignment;

interface AssignmentEngineInterface
{
    public function assign(AssignmentData $data): SupportAssignment;
}
