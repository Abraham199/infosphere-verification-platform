<?php

namespace App\Domain\Developer\Services;

class ApiDocumentationService
{
    public function manifest(): array
    {
        return [
            'source' => 'versioned route metadata and OpenAPI schemas',
            'formats' => ['openapi-json', 'markdown'],
            'status' => 'foundation_only',
        ];
    }
}
