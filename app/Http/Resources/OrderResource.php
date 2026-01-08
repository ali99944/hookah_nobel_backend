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
            'user_id' => $this->user_id,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),

            'subtotal' => (float) $this->subtotal,
            'shipping_cost' => (float) $this->shipping_cost,
            'total' => (float) $this->total,

            'status' => $this->status,
            'tracking_number' => $this->tracking_number,

            // Reconstructing the CustomerInfo interface
            'customer' => [
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
                'address' => $this->customer_address,
                'city' => $this->customer_city,
                'email' => $this->customer_email,
            ],

            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
