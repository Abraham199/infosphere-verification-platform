<?php

namespace App\Domain\Developer\Services;

class SdkFoundationService
{
    public function manifest(): array
    {
        return [
            'strategy' => 'OpenAPI-first language-neutral SDK generation',
            'targets' => ['php', 'javascript', 'python', 'mobile'],
            'versioning' => 'SDK major versions follow public API major versions.',
            'status' => 'foundation_only',
        ];
    }
}
