<?php

namespace App\Domain\Developer\DTOs;

use App\Domain\Developer\Enums\TokenType;
use Illuminate\Support\Carbon;

class ApiTokenData
{
    /**
     * @param array<int, string> $scopes
     */
    public function __construct(
        public readonly string $name,
        public readonly TokenType $type = TokenType::PERSONAL_ACCESS,
        public readonly ?string $tenantId = null,
        public readonly ?string $apiClientId = null,
        public readonly ?string $userId = null,
        public readonly array $scopes = [],
        public readonly ?Carbon $expiresAt = null,
    ) {
    }
}
