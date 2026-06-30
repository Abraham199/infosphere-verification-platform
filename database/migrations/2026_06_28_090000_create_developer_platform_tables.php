<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developer_applications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('environment', 50)->default('production')->index();
            $table->string('status', 50)->default('active')->index();
            $table->json('redirect_uris')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('api_clients', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('developer_application_id')->nullable()->constrained('developer_applications')->nullOnDelete();
            $table->string('client_id')->unique();
            $table->string('name');
            $table->string('environment', 50)->default('production')->index();
            $table->string('status', 50)->default('active')->index();
            $table->json('allowed_scopes')->nullable();
            $table->json('allowed_ips')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('api_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('version')->unique();
            $table->string('status', 50)->default('active')->index();
            $table->boolean('is_default')->default(false)->index();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('deprecated_at')->nullable();
            $table->timestamp('sunsets_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('api_keys', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('api_client_id')->constrained('api_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('key_prefix')->index();
            $table->string('key_hash')->unique();
            $table->string('environment', 50)->default('production')->index();
            $table->string('status', 50)->default('active')->index();
            $table->json('scopes')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('api_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('api_client_id')->nullable()->constrained('api_clients')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('token_hash')->unique();
            $table->string('token_type', 50)->default('personal_access')->index();
            $table->string('status', 50)->default('active')->index();
            $table->json('scopes')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('api_rate_limits', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('api_client_id')->nullable()->constrained('api_clients')->cascadeOnDelete();
            $table->string('product_code', 100)->nullable()->index();
            $table->string('endpoint', 191)->nullable()->index();
            $table->unsignedInteger('sustained_limit');
            $table->unsignedInteger('burst_limit');
            $table->unsignedInteger('window_seconds')->default(60);
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'api_client_id', 'endpoint'], 'api_rate_tenant_client_endpoint_idx');
        });

        Schema::create('api_usage_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('api_client_id')->nullable()->constrained('api_clients')->nullOnDelete();
            $table->string('request_id')->unique();
            $table->string('api_version', 50)->index();
            $table->string('method', 10);
            $table->string('endpoint', 191)->index();
            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->boolean('rate_limited')->default(false)->index();
            $table->string('product_code', 100)->nullable()->index();
            $table->string('error_code', 100)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'api_version', 'created_at']);
            $table->index(['api_client_id', 'created_at']);
        });

        Schema::create('webhook_endpoints', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('api_client_id')->nullable()->constrained('api_clients')->cascadeOnDelete();
            $table->string('environment', 50)->default('production')->index();
            $table->string('url');
            $table->string('secret_hash');
            $table->json('subscribed_events');
            $table->string('status', 50)->default('active')->index();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'url', 'environment'], 'webhook_endpoint_unique');
        });

        Schema::create('webhook_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('event_type', 150)->index();
            $table->string('event_reference')->unique();
            $table->json('payload');
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('webhook_event_id')->constrained('webhook_events')->cascadeOnDelete();
            $table->foreignUuid('webhook_endpoint_id')->constrained('webhook_endpoints')->cascadeOnDelete();
            $table->string('delivery_reference')->unique();
            $table->string('status', 50)->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(5);
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['webhook_endpoint_id', 'status', 'next_retry_at'], 'webhook_delivery_endpoint_status_retry_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('webhook_endpoints');
        Schema::dropIfExists('api_usage_logs');
        Schema::dropIfExists('api_rate_limits');
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('api_versions');
        Schema::dropIfExists('api_clients');
        Schema::dropIfExists('developer_applications');
    }
};
