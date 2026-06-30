<?php

namespace App\Http\Controllers\Internal\V1;

use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class InternalApiController extends Controller
{
    public function health(): JsonResponse
    {
        return ApiResponse::success([
            'api_version' => 'v1',
            'environment' => 'internal',
            'status' => 'ready',
        ]);
    }
}
