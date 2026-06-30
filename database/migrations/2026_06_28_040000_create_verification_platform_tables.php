<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_services', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('service_code')->unique();
            $table->text('description')->nullable();
            $table->decimal('default_price', 20, 2);
            $table->decimal('global_price', 20, 2)->nullable();
            $table->decimal('cost_price', 20, 2)->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->string('status')->default('active')->index();
            $table->boolean('supports_reservation')->default(true);
            $table->json('required_fields')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('provider_services', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('verification_service_id')->constrained('verification_services')->cascadeOnDelete();
            $table->string('provider')->index();
            $table->string('provider_service_code');
            $table->string('status')->default('active')->index();
            $table->unsignedInteger('priority')->default(100)->index();
            $table->json('capabilities')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_service_code']);
            $table->index(['verification_service_id', 'provider', 'status']);
        });

        Schema::create('verification_pricing_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('verification_service_id')->constrained('verification_services')->cascadeOnDelete();
            $table->decimal('price', 20, 2);
            $table->string('currency', 3)->default('NGN');
            $table->string('status')->default('active')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'verification_service_id', 'status'], 'ver_price_tenant_service_status_idx');
        });

        Schema::create('verification_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignUuid('verification_service_id')->constrained('verification_services')->restrictOnDelete();
            $table->foreignUuid('provider_service_id')->nullable()->constrained('provider_services')->nullOnDelete();
            $table->foreignUuid('wallet_reservation_id')->nullable()->constrained('wallet_reservations')->nullOnDelete();
            $table->foreignUuid('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();
            $table->foreignUuid('ledger_batch_id')->nullable()->constrained('ledger_batches')->nullOnDelete();
            $table->string('provider')->nullable()->index();
            $table->string('reference')->unique();
            $table->string('provider_reference')->nullable()->unique();
            $table->string('idempotency_key')->nullable()->unique();
            $table->decimal('price_charged', 20, 2);
            $table->string('currency', 3);
            $table->string('status')->index();
            $table->string('subject_identifier_hash')->nullable()->index();
            $table->json('request_payload')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['provider', 'status']);
        });

        Schema::create('verification_results', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('verification_request_id')->unique()->constrained('verification_requests')->cascadeOnDelete();
            $table->string('result_status')->index();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->text('summary')->nullable();
            $table->json('normalized_data')->nullable();
            $table->json('provider_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('verification_provider_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('verification_request_id')->nullable()->constrained('verification_requests')->nullOnDelete();
            $table->string('provider')->index();
            $table->string('request_reference')->nullable()->index();
            $table->string('response_reference')->nullable()->index();
            $table->string('status')->index();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->string('error_message')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamps();

            $table->index(['provider', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_provider_logs');
        Schema::dropIfExists('verification_results');
        Schema::dropIfExists('verification_requests');
        Schema::dropIfExists('verification_pricing_rules');
        Schema::dropIfExists('provider_services');
        Schema::dropIfExists('verification_services');
    }
};
