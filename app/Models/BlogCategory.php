<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    /** @use HasFactory<\Database\Factories\BlogCategoryFactory> */
    use HasFactory;

    protected $table = 'blog_categories';

    protected $guarded = [];

    public function sub_blog_categories()
    {
        return $this->hasMany(BlogCategory::class, 'parent_code', localKey: 'code');
    }
}
