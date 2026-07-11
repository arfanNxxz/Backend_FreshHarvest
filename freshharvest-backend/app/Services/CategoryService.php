<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->categoryRepository->all();
    }

    public function getById(int $id): Category
    {
        $category = $this->categoryRepository->find($id);

        if (! $category) {
            throw ValidationException::withMessages([
                'category' => 'Kategori tidak ditemukan.',
            ]);
        }

        return $category;
    }

    public function create(array $data): Category
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->categoryRepository->create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = $this->getById($id);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->categoryRepository->update($category, $data);
    }

    public function delete(int $id): bool
    {
        $category = $this->getById($id);
        return $this->categoryRepository->delete($category);
    }
}