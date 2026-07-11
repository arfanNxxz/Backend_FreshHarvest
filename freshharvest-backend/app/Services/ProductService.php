<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getPaginated(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return $this->productRepository->paginate($filters, $perPage);
    }

    public function getById(int $id): Product
    {
        $product = $this->productRepository->find($id);

        if (! $product) {
            throw ValidationException::withMessages([
                'product' => 'Produk tidak ditemukan.',
            ]);
        }

        return $product;
    }

    public function getBySlug(string $slug): Product
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            throw ValidationException::withMessages([
                'product' => 'Produk tidak ditemukan.',
            ]);
        }

        return $product;
    }

    public function create(array $data, int $userId): Product
    {
        $data['user_id'] = $userId;
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        $data['status'] = 'pending'; // menunggu approval admin

        $images = $data['images'] ?? [];
        $certifications = $data['certifications'] ?? [];
        unset($data['images'], $data['certifications']);

        $product = $this->productRepository->create($data);

        $this->attachImages($product, $images);
        $this->attachCertifications($product, $certifications);

        return $product->load(['category', 'supplier', 'images', 'certifications']);
    }

    public function update(int $id, array $data, int $userId): Product
    {
        $product = $this->getById($id);

        if ($product->user_id !== $userId) {
            throw ValidationException::withMessages([
                'product' => 'Anda tidak memiliki akses untuk mengubah produk ini.',
            ]);
        }

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        }

        $images = $data['images'] ?? null;
        unset($data['images'], $data['certifications']);

        $product = $this->productRepository->update($product, $data);

        if ($images) {
            $this->attachImages($product, $images);
        }

        return $product->load(['category', 'supplier', 'images', 'certifications']);
    }

    public function delete(int $id, int $userId): bool
    {
        $product = $this->getById($id);

        if ($product->user_id !== $userId) {
            throw ValidationException::withMessages([
                'product' => 'Anda tidak memiliki akses untuk menghapus produk ini.',
            ]);
        }

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        return $this->productRepository->delete($product);
    }

    protected function attachImages(Product $product, array $images): void
    {
        foreach ($images as $index => $image) {
            if ($image instanceof UploadedFile) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        }
    }

    protected function attachCertifications(Product $product, array $certifications): void
    {
        foreach ($certifications as $name) {
            $product->certifications()->create(['name' => $name]);
        }
    }
}