<?php

namespace App\Domain\Developer\Services;

use App\Domain\Developer\DTOs\ApiClientData;
use App\Domain\Developer\DTOs\ApiKeyData;
use App\Domain\Developer\DTOs\ApiTokenData;
use App\Domain\Developer\Enums\ApiCredentialStatus;
use App\Domain\Developer\Events\ApiKeyCreated;
use App\Domain\Developer\Events\ApiKeyRevoked;
use App\Domain\Developer\Exceptions\InvalidApiCredentialException;
use App\Domain\Developer\Interfaces\ApiCredentialServiceInterface;
use App\Domain\Developer\Interfaces\DeveloperRepositoryInterface;
use App\Domain\Developer\Models\ApiClient;
use App\Domain\Developer\Models\ApiKey;
use App\Domain\Developer\Models\ApiToken;
use Illuminate\Support\Str;

class ApiCredentialService implements ApiCredentialServiceInterface
{
    public function __construct(private readonly DeveloperRepositoryInterface $developers)
    {
    }

    public function createClient(ApiClientData $data): ApiClient
    {
        return $this->developers->createClient([
            'tenant_id' => $data->tenantId,
            'developer_application_id' => $data->developerApplicationId,
            'client_id' => 'cli_'.Str::lower((string) Str::ulid()),
            'name' => $data->name,
            'environment' => $data->environment,
            'status' => ApiCredentialStatus::ACTIVE,
            'allowed_scopes' => $data->allowedScopes,
            'allowed_ips' => $data->allowedIps,
        ]);
    }

    public function issueApiKey(ApiKeyData $data): array
    {
        $plainText = 'ivp_'.$data->environment->value.'_'.Str::random(48);
        $apiKey = $this->developers->createApiKey([
            'tenant_id' => $data->tenantId,
            'api_client_id' => $data->apiClientId,
            'name' => $data->name,
            'key_prefix' => substr($plainText, 0, 16),
            'key_hash' => hash('sha256', $plainText),
            'environment' => $data->environment,
            'status' => ApiCredentialStatus::ACTIVE,
            'scopes' => $data->scopes,
            'expires_at' => $data->expiresAt,
        ]);

        event(new ApiKeyCreated($apiKey));

        return ['plain_text_key' => $plainText, 'api_key' => $apiKey];
    }

    public function validateApiKey(string $plainTextKey): ApiKey
    {
        $apiKey = $this->developers->findApiKeyByHash(hash('sha256', $plainTextKey));

        if ($apiKey === null || $apiKey->status !== ApiCredentialStatus::ACTIVE || ($apiKey->expires_at !== null && now()->greaterThan($apiKey->expires_at))) {
            throw new InvalidApiCredentialException('Invalid or expired API key.');
        }

        $apiKey->forceFill(['last_used_at' => now()])->save();

        return $apiKey;
    }

    public function issueToken(ApiTokenData $data): array
    {
        $plainText = 'pat_'.Str::random(64);
        $token = $this->developers->createToken([
            'tenant_id' => $data->tenantId,
            'api_client_id' => $data->apiClientId,
            'user_id' => $data->userId,
            'name' => $data->name,
            'token_hash' => hash('sha256', $plainText),
            'token_type' => $data->type,
            'status' => ApiCredentialStatus::ACTIVE,
            'scopes' => $data->scopes,
            'expires_at' => $data->expiresAt,
        ]);

        return ['plain_text_token' => $plainText, 'token' => $token];
    }

    public function validateToken(string $plainTextToken): ApiToken
    {
        $token = $this->developers->findTokenByHash(hash('sha256', $plainTextToken));

        if ($token === null || $token->status !== ApiCredentialStatus::ACTIVE || ($token->expires_at !== null && now()->greaterThan($token->expires_at))) {
            throw new InvalidApiCredentialException('Invalid or expired API token.');
        }

        $token->forceFill(['last_used_at' => now()])->save();

        return $token;
    }

    public function revokeApiKey(ApiKey $key): ApiKey
    {
        $key->forceFill([
            'status' => ApiCredentialStatus::REVOKED,
            'revoked_at' => now(),
        ])->save();

        event(new ApiKeyRevoked($key));

        return $key;
    }
}
