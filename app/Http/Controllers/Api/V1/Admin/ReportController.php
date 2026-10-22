<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function dashboard(): JsonResponse
    {
        return response()->json(['data' => $this->reports->dashboard()]);
    }

    public function sales(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
        ]);

        return response()->json(['data' => $this->reports->sales($filters)]);
    }
}
