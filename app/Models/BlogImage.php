<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogImage extends Model
{
    /** @use HasFactory<\Database\Factories\BlogImageFactory> */
    use HasFactory;

    protected $table = 'blog_images';

    protected $guarded = [];


    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id', 'id');
    }
}
