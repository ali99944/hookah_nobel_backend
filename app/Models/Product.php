<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description',
        'price', 'stock', 'status', 'cover_image'
    ];

    // Accessors
    public function getCoverImageUrlAttribute()
    {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }

    // Relationships
    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function gallery() {
        return $this->hasMany(ProductGalleryImage::class);
    }

    public function attributes() {
        return $this->hasMany(ProductAttribute::class);
    }

    public function features() {
        return $this->hasMany(ProductFeature::class);
    }
}
