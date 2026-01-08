<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seo extends Model
{
    use HasFactory;
    protected $fillable = [
        'key', 'title', 'description', 'keywords',
        'og_title', 'og_description', 'og_image', 'og_type'
    ];

    protected $casts = ['structured_data' => 'array'];
}
