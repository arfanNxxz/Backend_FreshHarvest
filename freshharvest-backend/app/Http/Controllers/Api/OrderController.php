<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getUserOrders($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pesanan berhasil diambil.',
            'data' => OrderResource::collection($orders),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getById($id, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil.',
            'data' => new OrderResource($order),
        ]);
    }

    public function store(CheckoutRequest $request): JsonResponse
    {
        $order = $this->orderService->checkout(
            $request->user()->id,
            $request->only(['recipient_name', 'phone', 'street', 'suite', 'city_state_zip']),
            $request->validated('payment_method'),
            $request->validated('items')
        );

        return response()->json([
            'success' => true,
            'message' => 'Checkout berhasil, pesanan telah dibuat.',
            'data' => new OrderResource($order),
        ], 201);
    }

    public function confirmPayment(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->confirmPayment($id, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dikonfirmasi.',
            'data' => new OrderResource($order),
        ]);
    }
}