<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AirconUnitType extends Model
{
    protected $fillable = [
        'name', 'slug', 'price_centavos', 'technician_share_centavos', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_centavos' => 'integer',
            'technician_share_centavos' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getGrossCentavosAttribute(): int
    {
        return max($this->price_centavos - $this->technician_share_centavos, 0);
    }

    public function getFormattedPriceAttribute(): string
    {
        return "\u{20B1}".number_format($this->price_centavos / 100);
    }

    public function getFormattedTechnicianShareAttribute(): string
    {
        return "\u{20B1}".number_format($this->technician_share_centavos / 100);
    }

    public function getFormattedGrossAttribute(): string
    {
        return "\u{20B1}".number_format($this->gross_centavos / 100);
    }
}
