<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('type')->index();
            $table->string('currency', 3);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code', 'currency']);
            $table->index(['tenant_id', 'type']);
        });

        Schema::create('ledger_batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('description');
            $table->string('currency', 3);
            $table->decimal('total_debits', 20, 2)->default(0);
            $table->decimal('total_credits', 20, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->nullableUuidMorphs('source');
            $table->foreignUuid('reversal_of_batch_id')->nullable()->constrained('ledger_batches')->nullOnDelete();
            $table->timestamp('posted_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'posted_at']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('ledger_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('ledger_batch_id')->constrained('ledger_batches')->cascadeOnDelete();
            $table->foreignUuid('ledger_account_id')->constrained('ledger_accounts')->restrictOnDelete();
            $table->string('entry_reference')->unique();
            $table->string('type')->index();
            $table->string('status')->default('pending')->index();
            $table->decimal('amount', 20, 2);
            $table->string('currency', 3);
            $table->decimal('account_balance_after', 20, 2)->nullable();
            $table->foreignUuid('reversal_of_entry_id')->nullable()->constrained('ledger_entries')->nullOnDelete();
            $table->timestamp('posted_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'posted_at']);
            $table->index(['ledger_account_id', 'posted_at']);
            $table->index(['ledger_batch_id', 'type']);
        });

        Schema::create('reconciliation_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('scope');
            $table->string('status')->default('pending')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'scope', 'status']);
        });

        Schema::create('reconciliation_results', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('reconciliation_run_id')->constrained('reconciliation_runs')->cascadeOnDelete();
            $table->string('source_type');
            $table->string('source_reference')->nullable();
            $table->string('status')->index();
            $table->decimal('expected_amount', 20, 2)->default(0);
            $table->decimal('actual_amount', 20, 2)->default(0);
            $table->decimal('difference_amount', 20, 2)->default(0);
            $table->string('currency', 3)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['source_type', 'source_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_results');
        Schema::dropIfExists('reconciliation_runs');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_batches');
        Schema::dropIfExists('ledger_accounts');
    }
};
