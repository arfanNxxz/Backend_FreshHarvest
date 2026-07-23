<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'shipping' => [
                'recipient_name' => $this->recipient_name,
                'phone' => $this->phone,
                'street' => $this->street,
                'suite' => $this->suite,
                'city_state_zip' => $this->city_state_zip,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'subtotal' => (float) $this->subtotal,
            'shipping_cost' => (float) $this->shipping_cost,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'total_amount' => (float) $this->total,
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'created_at' => $this->created_at,
        ];
    }
}