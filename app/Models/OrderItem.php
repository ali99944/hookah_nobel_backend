<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name',
        'quantity', 'price', 'cover_image'
    ];

    public function getCoverImageUrlAttribute()
    {
        // Check if it's a full URL or a local path
        if (!$this->cover_image) return null;
        if (str_starts_with($this->cover_image, 'http')) return $this->cover_image;
        return Storage::url($this->cover_image);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
