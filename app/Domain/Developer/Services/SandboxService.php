<?php

namespace App\Domain\Developer\Services;

use App\Domain\Developer\DTOs\ApiClientData;
use App\Domain\Developer\DTOs\ApiKeyData;
use App\Domain\Developer\Enums\ApiCredentialStatus;
use App\Domain\Developer\Enums\DeveloperEnvironment;
use App\Domain\Developer\Events\SandboxApplicationCreated;
use App\Domain\Developer\Interfaces\ApiCredentialServiceInterface;
use App\Domain\Developer\Interfaces\DeveloperRepositoryInterface;
use Illuminate\Support\Str;

class SandboxService
{
    public function __construct(
        private readonly DeveloperRepositoryInterface $developers,
        private readonly ApiCredentialServiceInterface $credentials,
    ) {
    }

    public function createSandboxApplication(string $name, ?string $tenantId = null, ?string $ownerId = null): array
    {
        $application = $this->developers->createApplication([
            'tenant_id' => $tenantId,
            'owner_id' => $ownerId,
            'name' => $name,
            'slug' => 'sandbox-'.Str::slug($name).'-'.Str::lower((string) Str::ulid()),
            'environment' => DeveloperEnvironment::SANDBOX,
            'status' => ApiCredentialStatus::ACTIVE,
            'metadata' => ['sandbox' => true],
        ]);

        $client = $this->credentials->createClient(new ApiClientData(
            name: $name.' Sandbox Client',
            tenantId: $tenantId,
            developerApplicationId: $application->id,
            environment: DeveloperEnvironment::SANDBOX,
        ));

        $key = $this->credentials->issueApiKey(new ApiKeyData(
            apiClientId: $client->id,
            name: 'Default Sandbox Key',
            tenantId: $tenantId,
            environment: DeveloperEnvironment::SANDBOX,
        ));

        event(new SandboxApplicationCreated($application));

        return ['application' => $application, 'client' => $client, 'api_key' => $key];
    }
}
