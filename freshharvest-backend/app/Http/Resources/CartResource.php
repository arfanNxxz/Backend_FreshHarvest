<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items', fn () => $this->items);

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($items ?? collect()),
            'total_items' => $items ? $items->count() : 0,
            'subtotal' => $items ? (float) $items->sum(fn ($item) => $item->product->price * $item->qty) : 0,
        ];
    }
}