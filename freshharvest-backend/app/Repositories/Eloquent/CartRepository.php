<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\Contracts\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
    public function getOrCreateForUser(int $userId): Cart
    {
        return Cart::with(['items.product.images'])->firstOrCreate(['user_id' => $userId]);
    }

    public function findItem(int $cartId, int $productId): ?CartItem
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();
    }

    public function addItem(int $cartId, int $productId, int $qty): CartItem
    {
        return CartItem::create([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'qty' => $qty,
        ]);
    }

    public function updateItemQty(CartItem $item, int $qty): CartItem
    {
        $item->update(['qty' => $qty]);
        return $item;
    }

    public function removeItem(CartItem $item): bool
    {
        return $item->delete();
    }

    public function clear(int $cartId): void
    {
        CartItem::where('cart_id', $cartId)->delete();
    }
}