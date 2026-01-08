<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, // Cart Item ID
            'quantity' => $this->quantity,
            'product' => new ProductResource($this->whenLoaded('product')), // Use a summary resource for product details
            // 'addons_data' => $this->addons_data, // Add if using addons
            'line_total' => $this->whenLoaded('product', fn() => round($this->product->sell_price * $this->quantity, 2)), // Calculate line total
        ];
    }
}
