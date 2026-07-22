<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveProductRequest;
use App\Http\Resources\SavedProductResource;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $saved = $this->wishlistService->getAll($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk favorit berhasil diambil.',
            'data' => SavedProductResource::collection($saved),
        ]);
    }

    public function store(SaveProductRequest $request): JsonResponse
    {
        $this->wishlistService->add($request->user()->id, $request->validated('product_id'));

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke favorit.',
        ], 201);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        $this->wishlistService->remove($request->user()->id, $productId);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus dari favorit.',
        ]);
    }
}