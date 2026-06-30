<?php

namespace App\Domain\Support\Events;

use App\Domain\Support\Models\KnowledgeBaseArticle;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KnowledgeArticlePublished
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly KnowledgeBaseArticle $article)
    {
    }
}
