<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'content',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }

    // Relationship: Comment belongs to User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: Comment belongs to Post
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Scope: Only approved comments
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    // Scope: Pending approval
    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_approved', false);
    }

    // Scope: Recent comments
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
