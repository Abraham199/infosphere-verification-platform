<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('event_type', 150)->index();
            $table->string('channel', 50)->index();
            $table->string('subject')->nullable();
            $table->string('title')->nullable();
            $table->text('body');
            $table->json('variables')->nullable();
            $table->string('status', 50)->default('active')->index();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_default')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'event_type', 'channel', 'status']);
            $table->unique(['tenant_id', 'event_type', 'channel', 'version'], 'notification_template_version_unique');
        });

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('notification_type', 150)->index();
            $table->string('channel', 50)->index();
            $table->string('category', 50)->default('service')->index();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_mandatory')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'notification_type', 'channel'], 'notification_preference_lookup');
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 150)->index();
            $table->string('category', 50)->default('service')->index();
            $table->string('priority', 50)->default('normal')->index();
            $table->string('status', 50)->default('queued')->index();
            $table->string('subject')->nullable();
            $table->string('title')->nullable();
            $table->text('body');
            $table->json('data')->nullable();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'event_type', 'status']);
            $table->index(['user_id', 'status', 'created_at']);
        });

        Schema::create('notification_deliveries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel', 50)->index();
            $table->string('recipient');
            $table->string('delivery_reference')->unique();
            $table->string('status', 50)->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('provider_message_id')->nullable()->index();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'channel', 'status']);
            $table->index(['notification_id', 'channel']);
        });

        Schema::create('notification_channel_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('notification_delivery_id')->nullable()->constrained('notification_deliveries')->nullOnDelete();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('channel', 50)->index();
            $table->string('direction', 50)->default('outbound')->index();
            $table->string('status', 50)->index();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'channel', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_channel_logs');
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_templates');
    }
};
