<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Developer\Services\ApiDocumentationService;
use App\Domain\Developer\Services\SdkFoundationService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class DeveloperPlatformController extends Controller
{
    public function index(SdkFoundationService $sdk, ApiDocumentationService $docs): JsonResponse
    {
        return ApiResponse::success([
            'api_version' => 'v1',
            'environment' => 'public',
            'sdk' => $sdk->manifest(),
            'documentation' => $docs->manifest(),
        ]);
    }
}
