<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TariffRate;
use Illuminate\Http\JsonResponse;

class TariffController extends Controller
{
    public function index(): JsonResponse
    {
        $tariffs = TariffRate::where('is_active', true)
            ->orderBy('power_limit_va', 'asc')
            ->get();

        return response()->json([
            'tariffs' => $tariffs,
        ]);
    }
}
