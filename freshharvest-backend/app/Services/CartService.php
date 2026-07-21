<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository
    ) {}

    public function getCart(int $userId): Cart
    {
        return $this->cartRepository->getOrCreateForUser($userId);
    }

    public function addItem(int $userId, int $productId, int $qty): Cart
    {
        $product = Product::find($productId);

        if (! $product || ! $product->is_active || $product->status !== 'approved') {
            throw ValidationException::withMessages([
                'product' => 'Produk tidak tersedia.',
            ]);
        }

        if ($qty < $product->min_order_qty) {
            throw ValidationException::withMessages([
                'qty' => "Jumlah minimal pembelian adalah {$product->min_order_qty} {$product->unit}.",
            ]);
        }

        if ($qty > $product->stock) {
            throw ValidationException::withMessages([
                'qty' => 'Jumlah melebihi stok yang tersedia.',
            ]);
        }

        $cart = $this->cartRepository->getOrCreateForUser($userId);
        $existingItem = $this->cartRepository->findItem($cart->id, $productId);

        if ($existingItem) {
            $newQty = $existingItem->qty + $qty;

            if ($newQty > $product->stock) {
                throw ValidationException::withMessages([
                    'qty' => 'Jumlah total di keranjang melebihi stok yang tersedia.',
                ]);
            }

            $this->cartRepository->updateItemQty($existingItem, $newQty);
        } else {
            $this->cartRepository->addItem($cart->id, $productId, $qty);
        }

        return $this->cartRepository->getOrCreateForUser($userId);
    }

    public function updateItem(int $userId, int $itemId, int $qty): Cart
    {
        $cart = $this->cartRepository->getOrCreateForUser($userId);
        $item = $cart->items()->where('id', $itemId)->first();

        if (! $item) {
            throw ValidationException::withMessages([
                'item' => 'Item keranjang tidak ditemukan.',
            ]);
        }

        if ($qty > $item->product->stock) {
            throw ValidationException::withMessages([
                'qty' => 'Jumlah melebihi stok yang tersedia.',
            ]);
        }

        $this->cartRepository->updateItemQty($item, $qty);

        return $this->cartRepository->getOrCreateForUser($userId);
    }

    public function removeItem(int $userId, int $itemId): Cart
    {
        $cart = $this->cartRepository->getOrCreateForUser($userId);
        $item = $cart->items()->where('id', $itemId)->first();

        if (! $item) {
            throw ValidationException::withMessages([
                'item' => 'Item keranjang tidak ditemukan.',
            ]);
        }

        $this->cartRepository->removeItem($item);

        return $this->cartRepository->getOrCreateForUser($userId);
    }
}