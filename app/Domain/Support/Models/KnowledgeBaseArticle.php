<?php

namespace App\Domain\Support\Models;

use App\Domain\Support\Enums\KnowledgeArticleStatus;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowledgeBaseArticle extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = ['knowledge_base_category_id', 'tenant_id', 'author_id', 'slug', 'title', 'body', 'status', 'version', 'published_at', 'metadata'];

    protected function casts(): array
    {
        return [
            'status' => KnowledgeArticleStatus::class,
            'version' => 'integer',
            'published_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseCategory::class, 'knowledge_base_category_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
