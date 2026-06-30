<?php

namespace App\Domain\Product\Models;

use App\Domain\Product\Enums\ProductCategoryStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'status', 'display_order', 'metadata'];

    protected function casts(): array
    {
        return ['status' => ProductCategoryStatus::class, 'metadata' => 'array'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
