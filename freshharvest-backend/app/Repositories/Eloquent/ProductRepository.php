<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['category', 'supplier', 'images', 'certifications'])
            ->where('is_active', true)
            ->where('status', 'approved');

        if (! empty($filters['category_slug'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category_slug']));
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (! empty($filters['supplier_id'])) {
            $query->where('user_id', $filters['supplier_id']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating' => $query->orderBy('rating_avg', 'desc'),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Product
    {
        return Product::with(['category', 'supplier', 'images', 'certifications', 'reviews'])->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::with(['category', 'supplier', 'images', 'certifications', 'reviews'])
            ->where('slug', $slug)
            ->first();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product;
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}