<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('value_type')->default('string');
            $table->string('group')->default('general')->index();
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'group', 'key']);
        });

        Schema::create('tenant_branding', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 20)->default('#145DA0');
            $table->string('secondary_color', 20)->default('#071827');
            $table->string('accent_color', 20)->default('#19B6D2');
            $table->boolean('dark_mode_enabled')->default(false);
            $table->boolean('custom_css_allowed')->default(false);
            $table->timestamps();
        });

        Schema::create('tenant_domains', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->string('domain_type')->default('subdomain');
            $table->boolean('is_primary')->default(false);
            $table->string('ssl_status')->default('pending');
            $table->string('verification_status')->default('pending')->index();
            $table->timestamps();

            $table->index(['tenant_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_domains');
        Schema::dropIfExists('tenant_branding');
        Schema::dropIfExists('tenant_settings');
    }
};
