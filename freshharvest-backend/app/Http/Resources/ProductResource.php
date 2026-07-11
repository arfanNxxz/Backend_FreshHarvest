<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => (float) $this->price,
            'unit' => $this->unit,
            'stock' => $this->stock,
            'min_order_qty' => $this->min_order_qty,
            'export_grade' => $this->export_grade,
            'origin_region' => $this->origin_region,
            'rating_avg' => (float) $this->rating_avg,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'supplier' => [
                'id' => $this->whenLoaded('supplier', fn () => $this->supplier->id),
                'name' => $this->whenLoaded('supplier', fn () => $this->supplier->name),
            ],
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'certifications' => $this->whenLoaded('certifications', fn () => $this->certifications->pluck('name')),
            'created_at' => $this->created_at,
        ];
    }
}