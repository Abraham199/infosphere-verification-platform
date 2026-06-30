<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignUuid('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();
            $table->foreignUuid('ledger_batch_id')->nullable()->constrained('ledger_batches')->nullOnDelete();
            $table->string('provider')->index();
            $table->string('reference')->unique();
            $table->string('provider_reference')->nullable()->unique();
            $table->string('idempotency_key')->nullable()->unique();
            $table->decimal('amount', 20, 2);
            $table->string('currency', 3);
            $table->string('status')->index();
            $table->string('payment_method')->nullable()->index();
            $table->string('authorization_url')->nullable();
            $table->timestamp('initialized_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
        });

        Schema::create('payment_attempts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('payment_transaction_id')->constrained('payment_transactions')->cascadeOnDelete();
            $table->string('provider')->index();
            $table->string('provider_reference')->nullable()->index();
            $table->string('status')->index();
            $table->unsignedInteger('attempt_number');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();

            $table->unique(['payment_transaction_id', 'attempt_number']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('payment_webhooks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->string('provider')->index();
            $table->string('event_type')->index();
            $table->string('event_reference')->nullable()->unique();
            $table->string('signature_hash')->nullable()->index();
            $table->string('status')->index();
            $table->timestamp('received_at')->index();
            $table->timestamp('processed_at')->nullable();
            $table->json('payload')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['provider', 'received_at']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('payment_provider_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('payment_transaction_id')->nullable()->constrained('payment_transactions')->nullOnDelete();
            $table->string('provider')->index();
            $table->string('direction')->index();
            $table->string('action')->index();
            $table->string('status')->index();
            $table->unsignedInteger('http_status')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['provider', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_provider_logs');
        Schema::dropIfExists('payment_webhooks');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payment_transactions');
    }
};
