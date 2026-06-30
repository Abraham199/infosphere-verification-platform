<?php

namespace App\Domain\Support\Services;

use App\Domain\Support\DTOs\KnowledgeArticleData;
use App\Domain\Support\Enums\KnowledgeArticleStatus;
use App\Domain\Support\Events\KnowledgeArticlePublished;
use App\Domain\Support\Interfaces\KnowledgeBaseServiceInterface;
use App\Domain\Support\Interfaces\SupportRepositoryInterface;
use App\Domain\Support\Models\KnowledgeBaseArticle;

class KnowledgeBaseService implements KnowledgeBaseServiceInterface
{
    public function __construct(private readonly SupportRepositoryInterface $support)
    {
    }

    public function createArticle(KnowledgeArticleData $data): KnowledgeBaseArticle
    {
        return $this->support->createKnowledgeArticle([
            'knowledge_base_category_id' => $data->categoryId,
            'tenant_id' => $data->tenantId,
            'author_id' => $data->authorId,
            'slug' => $data->slug,
            'title' => $data->title,
            'body' => $data->body,
            'status' => KnowledgeArticleStatus::DRAFT,
            'version' => 1,
            'metadata' => $data->metadata,
        ]);
    }

    public function publish(KnowledgeBaseArticle $article): KnowledgeBaseArticle
    {
        $article->forceFill([
            'status' => KnowledgeArticleStatus::PUBLISHED,
            'published_at' => now(),
        ])->save();

        event(new KnowledgeArticlePublished($article));

        return $article;
    }
}
