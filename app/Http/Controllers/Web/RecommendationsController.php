<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesHouseholdContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Rekomendasi Penghematan Energi: every recommendation with its kWh and rupiah savings.
 */
class RecommendationsController extends Controller
{
    use ResolvesHouseholdContext;

    public function index(Request $request): View
    {
        return view('recommendations.index', $this->pageData($request));
    }
}
