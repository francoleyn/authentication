<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'avatar',
        'phone',
        'address',
        'date_of_birth',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    // Relationship: Profile belongs to User (One-to-One inverse)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessor: Get full address
    public function getFullAddressAttribute(): string
    {
        return $this->address ?? 'No address provided';
    }

    // Accessor: Get age from date of birth
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }
}
