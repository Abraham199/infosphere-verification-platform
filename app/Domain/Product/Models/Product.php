<?php

namespace App\Domain\Product\Models;

use App\Domain\Product\Enums\ProductStatus;
use App\Domain\Product\Enums\ProductVisibility;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = ['product_category_id', 'code', 'name', 'description', 'default_price', 'cost_price', 'selling_price', 'currency', 'status', 'visibility', 'metadata'];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'status' => ProductStatus::class,
            'visibility' => ProductVisibility::class,
            'metadata' => 'array',
        ];
    }

    public function category(): BelongsTo { return $this->belongsTo(ProductCategory::class, 'product_category_id'); }
    public function providerMappings(): HasMany { return $this->hasMany(ProductProviderMapping::class); }
    public function tenantProducts(): HasMany { return $this->hasMany(TenantProduct::class); }
    public function features(): HasMany { return $this->hasMany(ProductFeature::class); }
    public function capabilities(): HasMany { return $this->hasMany(ProductCapabilitySetting::class); }
}
