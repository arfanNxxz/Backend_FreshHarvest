<?php

namespace App\Services;

use App\Models\SavedProduct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class WishlistService
{
    public function getAll(int $userId): Collection
    {
        return SavedProduct::with(['product.category', 'product.images'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function add(int $userId, int $productId): SavedProduct
    {
        $existing = SavedProduct::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'product' => 'Produk sudah ada di daftar favorit.',
            ]);
        }

        return SavedProduct::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }

    public function remove(int $userId, int $productId): bool
    {
        $saved = SavedProduct::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if (! $saved) {
            throw ValidationException::withMessages([
                'product' => 'Produk tidak ditemukan di daftar favorit.',
            ]);
        }

        return $saved->delete();
    }
}