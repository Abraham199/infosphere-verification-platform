<?php

namespace App\Domain\Product\Models;

use App\Domain\Product\Enums\ProductStatus;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantProduct extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'product_id', 'is_enabled', 'selling_price_override', 'status_override', 'access_rules', 'metadata'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'selling_price_override' => 'decimal:2', 'status_override' => ProductStatus::class, 'access_rules' => 'array', 'metadata' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
