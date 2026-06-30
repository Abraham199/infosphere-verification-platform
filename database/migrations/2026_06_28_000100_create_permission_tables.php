<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->string('module')->nullable()->index();
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['tenant_id', 'name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->string('model_type');
            $table->uuid('model_id');
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();

            $table->index(['model_id', 'model_type']);
            $table->unique(['tenant_id', 'permission_id', 'model_id', 'model_type'], 'model_has_permissions_unique');
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('model_type');
            $table->uuid('model_id');
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();

            $table->index(['model_id', 'model_type']);
            $table->unique(['tenant_id', 'role_id', 'model_id', 'model_type'], 'user_roles_unique');
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->foreignUuid('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();

            $table->primary(['permission_id', 'role_id'], 'role_permissions_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
