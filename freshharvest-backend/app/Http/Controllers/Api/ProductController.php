<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['category_slug', 'search', 'supplier_id', 'min_price', 'max_price', 'sort']);
        $products = $this->productService->getPaginated($filters, (int) $request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk berhasil diambil.',
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->productService->getBySlug($slug);

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil.',
            'data' => new ProductResource($product),
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
{
    $product = $this->productService->create(
        $request->validated() + ['images' => $request->file('images', [])],
        $request->user()->id
    );

    return response()->json([
        'success' => true,
        'message' => 'Produk berhasil ditambahkan.',
        'data' => new ProductResource($product),
    ], 201);
}

public function update(UpdateProductRequest $request, int $id): JsonResponse
{
    $product = $this->productService->update(
        $id,
        $request->validated() + ['images' => $request->file('images', [])],
        $request->user()->id
    );

    return response()->json([
        'success' => true,
        'message' => 'Produk berhasil diperbarui.',
        'data' => new ProductResource($product),
    ]);
}

public function destroy(Request $request, int $id): JsonResponse
{
    $this->productService->delete($id, $request->user()->id);

    return response()->json([
        'success' => true,
        'message' => 'Produk berhasil dihapus.',
    ]);
}
}