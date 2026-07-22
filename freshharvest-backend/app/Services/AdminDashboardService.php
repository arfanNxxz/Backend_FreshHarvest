<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class AdminDashboardService
{
    public function getStats(): array
    {
        $totalIncome = Order::whereHas('payment', fn ($q) => $q->where('status', 'paid'))->sum('total');

        return [
            'total_income' => (float) $totalIncome,
            'total_users' => User::role('user')->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'widgets' => [
                'shipping' => Order::where('status', 'dikirim')->count(),
                'cancelled' => Order::where('status', 'dibatalkan')->count(),
                'refund' => 0,
                'completed' => Order::where('status', 'selesai')->count(),
            ],
        ];
    }

    public function getRecentOrders(int $limit = 10)
    {
        return Order::with('user')->latest()->limit($limit)->get();
    }
}