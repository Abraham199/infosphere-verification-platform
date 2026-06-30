<?php

namespace App\Domain\Support\Interfaces;

use App\Domain\Support\DTOs\KnowledgeArticleData;
use App\Domain\Support\Models\KnowledgeBaseArticle;

interface KnowledgeBaseServiceInterface
{
    public function createArticle(KnowledgeArticleData $data): KnowledgeBaseArticle;
    public function publish(KnowledgeBaseArticle $article): KnowledgeBaseArticle;
}
