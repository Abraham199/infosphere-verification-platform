<?php

namespace App\Domain\Product\Models;

use App\Domain\Product\Enums\ProviderMappingStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductProviderMapping extends Model
{
    use HasUuid;

    protected $fillable = ['product_id', 'provider', 'provider_product_code', 'status', 'priority', 'configuration'];

    protected function casts(): array
    {
        return ['status' => ProviderMappingStatus::class, 'configuration' => 'array'];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
