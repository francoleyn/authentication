<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    // Relationship: Category belongs to many Posts (Many-to-Many)
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }

    // Scope: Has posts
    public function scopeHasPosts(Builder $query): Builder
    {
        return $query->has('posts');
    }

    // Scope: Order by posts count
    public function scopePopular(Builder $query): Builder
    {
        return $query->withCount('posts')->orderByDesc('posts_count');
    }

    // Accessor: Get posts count
    public function getPostsCountAttribute(): int
    {
        return $this->posts()->count();
    }
}
