<?php

namespace App\Domain\Verification\Models;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Verification\Enums\VerificationResultStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationResult extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'verification_request_id', 'result_status', 'confidence_score', 'summary', 'normalized_data', 'provider_payload'];

    protected function casts(): array
    {
        return [
            'result_status' => VerificationResultStatus::class,
            'confidence_score' => 'decimal:2',
            'normalized_data' => 'array',
            'provider_payload' => 'array',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function request(): BelongsTo { return $this->belongsTo(VerificationRequest::class, 'verification_request_id'); }
}
