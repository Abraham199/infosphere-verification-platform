<?php

namespace App\Domain\Product\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCapabilitySetting extends Model
{
    use HasUuid;

    protected $table = 'product_capabilities';

    protected $fillable = ['product_id', 'capability_key', 'is_enabled', 'configuration'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'configuration' => 'array'];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
