<?php

namespace App\Exceptions;

use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;
use Throwable;

class Handler
{
    public function renderApiException(Throwable $exception, Request $request)
    {
        if (! $request->expectsJson()) {
            return null;
        }

        $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;

        return ApiResponse::error($exception->getMessage() ?: 'Server error.', $status);
    }
}
