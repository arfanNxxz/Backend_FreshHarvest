<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'product_image' => $this->whenLoaded('product', fn () => $this->product?->images->first()?->image_path
                ? \Storage::disk('public')->url($this->product->images->first()->image_path)
                : null),
            'price' => (float) $this->price,
            'unit' => $this->unit,
            'qty' => $this->qty,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}