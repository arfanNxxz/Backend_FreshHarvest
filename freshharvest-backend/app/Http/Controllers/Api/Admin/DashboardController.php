<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminOrderResource;
use App\Services\AdminDashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected AdminDashboardService $dashboardService
    ) {}

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Statistik dashboard berhasil diambil.',
            'data' => $this->dashboardService->getStats(),
        ]);
    }

    public function recentOrders(): JsonResponse
    {
        $orders = $this->dashboardService->getRecentOrders();

        return response()->json([
            'success' => true,
            'message' => 'Order terbaru berhasil diambil.',
            'data' => AdminOrderResource::collection($orders),
        ]);
    }
}