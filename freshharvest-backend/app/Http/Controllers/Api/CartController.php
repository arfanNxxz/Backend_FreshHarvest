<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diambil.',
            'data' => new CartResource($cart),
        ]);
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $cart = $this->cartService->addItem(
            $request->user()->id,
            $request->validated('product_id'),
            $request->validated('qty')
        );

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
            'data' => new CartResource($cart),
        ], 201);
    }

    public function update(UpdateCartItemRequest $request, int $itemId): JsonResponse
    {
        $cart = $this->cartService->updateItem(
            $request->user()->id,
            $itemId,
            $request->validated('qty')
        );

        return response()->json([
            'success' => true,
            'message' => 'Keranjang berhasil diperbarui.',
            'data' => new CartResource($cart),
        ]);
    }

    public function destroy(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->cartService->removeItem($request->user()->id, $itemId);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari keranjang.',
            'data' => new CartResource($cart),
        ]);
    }
}