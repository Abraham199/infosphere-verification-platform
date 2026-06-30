<?php

namespace App\Domain\Support\Models;

use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowledgeBaseCategory extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'slug', 'name', 'description', 'is_active', 'display_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(KnowledgeBaseArticle::class);
    }
}
