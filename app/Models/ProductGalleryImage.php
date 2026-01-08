<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductGalleryImage extends Model
{
    protected $fillable = ['product_id', 'url'];

    public function getFullUrlAttribute()
    {
        return $this->url ? Storage::url($this->url) : null;
    }
}
