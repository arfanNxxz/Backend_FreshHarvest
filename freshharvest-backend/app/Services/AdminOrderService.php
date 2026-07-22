<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AdminOrderService
{
    public function getAll(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::with('user')->latest();

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->paginate($perPage);
    }

    public function updateStatus(int $orderId, string $status): Order
    {
        $order = Order::find($orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        $order->update(['status' => $status]);

        return $order->load(['items.product', 'payment', 'user']);
    }
}