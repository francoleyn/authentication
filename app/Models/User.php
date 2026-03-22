<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==================== ELOQUENT RELATIONSHIPS ====================

    // One-to-One: User has one Profile
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    // One-to-Many: User has many Posts
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // One-to-Many: User has many Comments
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // One-to-Many: User has many Files
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    // ==================== ACCESSORS ====================

    // Get posts count
    public function getPostsCountAttribute(): int
    {
        return $this->posts()->count();
    }

    // Get comments count
    public function getCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }

    // Get files count
    public function getFilesCountAttribute(): int
    {
        return $this->files()->count();
    }

    // Get total storage used
    public function getTotalStorageUsedAttribute(): int
    {
        return $this->files()->sum('size');
    }
}
