<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\ViewModels\ExperienceDashboardData;
use Illuminate\Contracts\View\View;

class VerificationController extends Controller
{
    public function __invoke(ExperienceDashboardData $data): View
    {
        return view('platform.verification.index', $data->verification());
    }
}
