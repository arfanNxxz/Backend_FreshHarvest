<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;
use App\Models\CartItem;

interface CartRepositoryInterface
{
    public function getOrCreateForUser(int $userId): Cart;

    public function findItem(int $cartId, int $productId): ?CartItem;

    public function addItem(int $cartId, int $productId, int $qty): CartItem;

    public function updateItemQty(CartItem $item, int $qty): CartItem;

    public function removeItem(CartItem $item): bool;

    public function clear(int $cartId): void;
}