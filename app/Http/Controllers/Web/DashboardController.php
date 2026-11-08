<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesHouseholdContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard: summary information and KPIs only. Each detail lives on its own page.
 */
class DashboardController extends Controller
{
    use ResolvesHouseholdContext;

    public function index(Request $request): View
    {
        return view('dashboard', $this->pageData($request, withPrevious: true));
    }
}
