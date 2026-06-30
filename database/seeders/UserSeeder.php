<?php

namespace Database\Seeders;

use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('DEMO_USER_PASSWORD', 'ChangeMeSecurely123!');
        $tenant = Tenant::query()->where('slug', env('DEMO_TENANT_SLUG', 'demo'))->firstOrFail();

        $superAdmin = User::query()->firstOrCreate(
            ['email' => env('PLATFORM_SUPER_ADMIN_EMAIL', 'admin@infosphere.test')],
            [
                'name' => env('PLATFORM_SUPER_ADMIN_NAME', 'Platform Super Admin'),
                'password' => Hash::make(env('PLATFORM_SUPER_ADMIN_PASSWORD', 'ChangeMeSecurely123!')),
                'user_type' => 'platform',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
        $superAdmin->assignRole('Super Admin');

        $owner = User::query()->firstOrCreate(
            ['email' => env('DEMO_TENANT_OWNER_EMAIL', 'owner@demo.test')],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Tenant Owner',
                'password' => Hash::make($password),
                'user_type' => 'tenant',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
        $owner->assignRole('Tenant Owner');

        $admin = User::query()->firstOrCreate(
            ['email' => env('DEMO_TENANT_ADMIN_EMAIL', 'tenant-admin@demo.test')],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Tenant Admin',
                'password' => Hash::make($password),
                'user_type' => 'tenant',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
        $admin->assignRole('Tenant Admin');
    }
}
