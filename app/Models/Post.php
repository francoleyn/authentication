<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // Relationship: Post belongs to User (Many-to-One)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Alias for user relationship
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship: Post has many Comments (One-to-Many)
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Relationship: Post belongs to many Categories (Many-to-Many)
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    // Scope: Only published posts
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    // Scope: Only draft posts
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('is_published', false);
    }

    // Scope: Search by title or content
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('content', 'like', "%{$term}%");
        });
    }

    // Scope: Filter by category
    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    // Accessor: Get excerpt or truncated content
    public function getShortContentAttribute(): string
    {
        return $this->excerpt ?? str()->limit($this->content, 150);
    }

    // Accessor: Get comments count
    public function getCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }
}
