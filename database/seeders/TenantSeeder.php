<?php

namespace Database\Seeders;

use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => env('DEMO_TENANT_SLUG', 'demo')],
            [
                'name' => env('DEMO_TENANT_NAME', 'Info-Sphere Demo Tenant'),
                'legal_name' => 'Info-Sphere Demo Tenant Ltd',
                'email' => 'hello@demo.test',
                'status' => 'active',
                'plan_code' => 'foundation',
                'default_currency' => 'NGN',
                'timezone' => 'Africa/Lagos',
            ],
        );

        $tenant->branding()->firstOrCreate([], [
            'primary_color' => '#145DA0',
            'secondary_color' => '#071827',
            'accent_color' => '#19B6D2',
        ]);

        $tenant->domains()->firstOrCreate([
            'domain' => 'demo.localhost',
        ], [
            'domain_type' => 'subdomain',
            'is_primary' => true,
            'ssl_status' => 'local',
            'verification_status' => 'verified',
        ]);

        foreach (config('ivp.features') as $feature => $enabled) {
            $tenant->settings()->firstOrCreate([
                'group' => 'features',
                'key' => $feature,
            ], [
                'value' => $enabled ? 'true' : 'false',
                'value_type' => 'boolean',
            ]);
        }
    }
}
