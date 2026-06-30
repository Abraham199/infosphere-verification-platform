<?php

namespace Tests\Feature;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\TenantResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_be_resolved_from_path(): void
    {
        $tenant = Tenant::create([
            'name' => 'Demo',
            'slug' => 'demo',
            'status' => 'active',
        ]);

        $request = Request::create('/t/demo/dashboard');

        $resolved = app(TenantResolver::class)->resolve($request);

        $this->assertTrue($tenant->is($resolved));
    }
}
