<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeoResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'page_name' => $this->page_name,

            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,

            'og_image' => $this->og_image,
        ];
    }
}
