<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $displayStatus = $this->status;
        if ($this->is_paid && $this->status === 'pending') {
            $displayStatus = 'paid';
        }

        return [
            'id' => $this->id,
            'user_id' => null,
            'subtotal' => (float) $this->subtotal,
            'shipping_cost' => (float) $this->shipping_cost,
            'fees_cost' => (float) $this->fees_cost,
            'total' => (float) $this->total,
            'status' => $displayStatus,
            'is_paid' => (bool) $this->is_paid,
            'tracking_code' => $this->tracking_code,
            'tracking_number' => $this->tracking_code,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'address' => $this->address,
            'city' => $this->city,
            'notes' => $this->notes,
            'customer_address' => $this->address,
            'customer_city' => $this->city,
            'customer' => [
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
                'email' => $this->customer_email,
                'address' => $this->address,
                'city' => $this->city,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
