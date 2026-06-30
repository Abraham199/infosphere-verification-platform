<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('event_name')->index();
            $table->string('source_domain')->index();
            $table->string('event_reference')->unique();
            $table->timestamp('occurred_at')->index();
            $table->json('dimensions')->nullable();
            $table->json('measures')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'source_domain', 'occurred_at']);
            $table->index(['event_name', 'occurred_at']);
        });

        Schema::create('reporting_metrics', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('metric_key')->index();
            $table->string('category')->index();
            $table->decimal('value', 24, 6)->default(0);
            $table->string('unit')->nullable();
            $table->date('period_date')->index();
            $table->string('period_type')->default('daily')->index();
            $table->json('dimensions')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'metric_key', 'period_date', 'period_type'], 'reporting_metric_period_unique');
        });

        Schema::create('reporting_snapshots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('snapshot_key')->index();
            $table->string('period_type')->index();
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->json('data');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'snapshot_key', 'period_type', 'period_start'], 'reporting_snapshot_period_unique');
        });

        Schema::create('reporting_jobs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('report_key')->index();
            $table->string('frequency')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->json('parameters')->nullable();
            $table->timestamp('scheduled_for')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'report_key', 'status']);
        });

        Schema::create('reporting_exports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('reporting_job_id')->nullable()->constrained('reporting_jobs')->nullOnDelete();
            $table->string('report_key')->index();
            $table->string('format')->index();
            $table->string('status')->default('pending')->index();
            $table->string('export_reference')->unique();
            $table->json('parameters')->nullable();
            $table->string('storage_disk')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'report_key', 'created_at']);
        });

        Schema::create('reporting_audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index();
            $table->string('auditable_type')->nullable();
            $table->uuid('auditable_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'action', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_audit_logs');
        Schema::dropIfExists('reporting_exports');
        Schema::dropIfExists('reporting_jobs');
        Schema::dropIfExists('reporting_snapshots');
        Schema::dropIfExists('reporting_metrics');
        Schema::dropIfExists('analytics_events');
    }
};
