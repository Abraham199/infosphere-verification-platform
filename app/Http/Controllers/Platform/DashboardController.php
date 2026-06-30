<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\ViewModels\ExperienceDashboardData;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(ExperienceDashboardData $data): View
    {
        return view('platform.dashboard', $data->platform());
    }
}
