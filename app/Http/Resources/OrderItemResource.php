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
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'cover_image' => $this->cover_image_url ?? $this->product?->cover_image_url,
            'line_total' => round(((float) $this->price) * (int) $this->quantity, 2),
        ];
    }
}
