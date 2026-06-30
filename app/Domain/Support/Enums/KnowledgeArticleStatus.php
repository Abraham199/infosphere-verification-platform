<?php

namespace App\Domain\Support\Enums;

enum KnowledgeArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
