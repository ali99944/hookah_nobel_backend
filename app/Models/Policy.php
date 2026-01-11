<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    protected $table = 'policies';

    protected $fillable = [
        'key',
        'name',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords'
    ];
}
