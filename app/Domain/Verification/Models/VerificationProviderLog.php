<?php

namespace App\Domain\Verification\Models;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Verification\Enums\VerificationProvider;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationProviderLog extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'verification_request_id', 'provider', 'request_reference', 'response_reference', 'status', 'latency_ms', 'retry_count', 'error_message', 'request_payload', 'response_payload'];

    protected function casts(): array
    {
        return ['provider' => VerificationProvider::class, 'request_payload' => 'array', 'response_payload' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function request(): BelongsTo { return $this->belongsTo(VerificationRequest::class, 'verification_request_id'); }
}
