<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function find(int $id): ?Order
    {
        return Order::with(['items.product.images', 'payment'])->find($id);
    }

    public function findByUser(int $userId): Collection
    {
        return Order::with(['items.product.images', 'payment'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::with(['items.product.images', 'payment'])
            ->where('order_number', $orderNumber)
            ->first();
    }
}