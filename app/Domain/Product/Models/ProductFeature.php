<?php

namespace App\Domain\Product\Models;

use App\Domain\Product\Enums\ProductCategoryStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFeature extends Model
{
    use HasUuid;

    protected $fillable = ['product_id', 'feature_key', 'value_type', 'value', 'status', 'metadata'];

    protected function casts(): array
    {
        return ['status' => ProductCategoryStatus::class, 'metadata' => 'array'];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
