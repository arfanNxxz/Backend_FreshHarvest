<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;

    public function find(int $id): ?Order;

    public function findByUser(int $userId): Collection;

    public function findByOrderNumber(string $orderNumber): ?Order;
}