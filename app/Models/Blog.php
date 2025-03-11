<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    /** @use HasFactory<\Database\Factories\BlogFactory> */
    use HasFactory;

    protected $table = 'blogs';

    protected $guarded = [];

    public function blog_category() {
        return $this->belongsTo(BlogCategory::class, 'category_code', 'code');
    }

    // public function images()
    // {
    //     return $this->hasMany(BlogImage::class, 'blog_id', 'id');
    // }
}
