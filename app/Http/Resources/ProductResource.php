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
            'description' => $this->description,
            'price' => (float) $this->price,
            'status' => $this->status,
            'cover_image' => $this->cover_image_url,
            'collection' => new CategoryResource($this->whenLoaded('category')),
            'collection_id' => $this->category_id,

            // Relations
            'gallery' => $this->gallery->map(fn($img) => [
                'id' => $img->id,
                'url' => $img->full_url
            ]),
            'attributes' => $this->attributes->map(fn($attr) => [
                'id' => $attr->id,
                'key' => $attr->key,
                'value' => $attr->value
            ]),
            'features' => $this->features->map(fn($feat) => [
                'id' => $feat->id,
                'key' => $feat->key,
                'value' => $feat->value
            ]),
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
