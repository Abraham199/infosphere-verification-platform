<?php

namespace App\Domain\Developer\Interfaces;

use App\Domain\Developer\DTOs\ApiClientData;
use App\Domain\Developer\DTOs\ApiKeyData;
use App\Domain\Developer\DTOs\ApiTokenData;
use App\Domain\Developer\Models\ApiClient;
use App\Domain\Developer\Models\ApiKey;
use App\Domain\Developer\Models\ApiToken;

interface ApiCredentialServiceInterface
{
    public function createClient(ApiClientData $data): ApiClient;
    public function issueApiKey(ApiKeyData $data): array;
    public function validateApiKey(string $plainTextKey): ApiKey;
    public function issueToken(ApiTokenData $data): array;
    public function validateToken(string $plainTextToken): ApiToken;
    public function revokeApiKey(ApiKey $key): ApiKey;
}
