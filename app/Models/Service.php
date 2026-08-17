<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Service extends Model
{
    use HasFactory;

    public const STANDARD_SLUG = 'standard-clean';

    protected $fillable = [
        'name', 'slug', 'short_description', 'description', 'price_centavos',
        'duration_minutes', 'buffer_minutes', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_centavos' => 'integer',
            'duration_minutes' => 'integer',
            'buffer_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeBookable(Builder $query): Builder
    {
        return $query
            ->where('slug', self::STANDARD_SLUG)
            ->where('is_active', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        return "\u{20B1}".number_format($this->price_centavos / 100);
    }
}
