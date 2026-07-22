<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_name' => $this->whenLoaded('user', fn () => $this->user->name),
            'date' => $this->created_at->format('M d, Y'),
            'amount' => (float) $this->total,
            'status' => $this->status,
            'items_count' => $this->whenLoaded('items', fn () => $this->items->count()),
        ];
    }
}