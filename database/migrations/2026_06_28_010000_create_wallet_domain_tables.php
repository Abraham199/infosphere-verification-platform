<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('currency', 3);
            $table->decimal('available_balance', 20, 2)->default(0);
            $table->decimal('pending_balance', 20, 2)->default(0);
            $table->decimal('frozen_balance', 20, 2)->default(0);
            $table->decimal('reserved_balance', 20, 2)->default(0);
            $table->decimal('refund_balance', 20, 2)->default(0);
            $table->string('status')->default('active')->index();
            $table->timestamp('locked_at')->nullable();
            $table->string('locked_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'currency']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('wallet_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('account_type');
            $table->string('currency', 3);
            $table->decimal('balance', 20, 2)->default(0);
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->unique(['wallet_id', 'account_type']);
            $table->index(['tenant_id', 'account_type']);
        });

        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('type')->index();
            $table->string('status')->index();
            $table->decimal('amount', 20, 2);
            $table->string('currency', 3);
            $table->decimal('balance_before', 20, 2)->default(0);
            $table->decimal('balance_after', 20, 2)->default(0);
            $table->nullableMorphs('related');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'type', 'status']);
        });

        Schema::create('wallet_reservations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignUuid('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 20, 2);
            $table->decimal('captured_amount', 20, 2)->default(0);
            $table->decimal('released_amount', 20, 2)->default(0);
            $table->string('currency', 3);
            $table->string('status')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->nullableMorphs('related');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('wallet_adjustments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignUuid('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('direction');
            $table->decimal('amount', 20, 2);
            $table->string('currency', 3);
            $table->string('reason');
            $table->foreignUuid('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('status')->default('pending')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_adjustments');
        Schema::dropIfExists('wallet_reservations');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallet_accounts');
        Schema::dropIfExists('wallets');
    }
};
