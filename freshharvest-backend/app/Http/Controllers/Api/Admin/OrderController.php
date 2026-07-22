<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\AdminOrderResource;
use App\Http\Resources\OrderResource;
use App\Services\AdminOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected AdminOrderService $adminOrderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'search']);
        $orders = $this->adminOrderService->getAll($filters, (int) $request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar order berhasil diambil.',
            'data' => AdminOrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->adminOrderService->updateStatus($id, $request->validated('status'));

        return response()->json([
            'success' => true,
            'message' => 'Status order berhasil diperbarui.',
            'data' => new OrderResource($order),
        ]);
    }
}