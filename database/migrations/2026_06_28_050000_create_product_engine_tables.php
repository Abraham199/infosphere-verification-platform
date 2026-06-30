<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->unsignedInteger('display_order')->default(100)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_category_id')->constrained('product_categories')->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_price', 20, 2)->default(0);
            $table->decimal('cost_price', 20, 2)->nullable();
            $table->decimal('selling_price', 20, 2)->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->string('status')->default('draft')->index();
            $table->string('visibility')->default('private')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_category_id', 'status']);
        });

        Schema::create('product_provider_mappings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('provider')->index();
            $table->string('provider_product_code');
            $table->string('status')->default('active')->index();
            $table->unsignedInteger('priority')->default(100)->index();
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_product_code']);
            $table->index(['product_id', 'status', 'priority']);
        });

        Schema::create('tenant_products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false)->index();
            $table->decimal('selling_price_override', 20, 2)->nullable();
            $table->string('status_override')->nullable()->index();
            $table->json('access_rules')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'product_id']);
            $table->index(['tenant_id', 'is_enabled']);
        });

        Schema::create('product_features', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('feature_key');
            $table->string('value_type')->default('boolean');
            $table->text('value')->nullable();
            $table->string('status')->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'feature_key']);
        });

        Schema::create('product_capabilities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('capability_key');
            $table->boolean('is_enabled')->default(true);
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'capability_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_capabilities');
        Schema::dropIfExists('product_features');
        Schema::dropIfExists('tenant_products');
        Schema::dropIfExists('product_provider_mappings');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
