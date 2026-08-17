<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'reference', 'manage_token', 'customer_id', 'service_id', 'aircon_unit_type_id', 'technician_id', 'plan', 'unit_type',
        'interval_months', 'quantity', 'unit_price_centavos', 'price_per_visit_centavos',
        'technician_share_per_visit_centavos', 'gross_per_visit_centavos', 'status', 'next_service_date',
        'preferred_day', 'preferred_time', 'address_line', 'barangay', 'city', 'province',
        'postal_code', 'landmark', 'latitude', 'longitude', 'location_consent_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'next_service_date' => 'date',
            'location_consent_at' => 'datetime',
            'interval_months' => 'integer',
            'quantity' => 'integer',
            'price_per_visit_centavos' => 'integer',
            'unit_price_centavos' => 'integer',
            'technician_share_per_visit_centavos' => 'integer',
            'gross_per_visit_centavos' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function airconUnitType(): BelongsTo
    {
        return $this->belongsTo(AirconUnitType::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return "\u{20B1}".number_format($this->price_per_visit_centavos / 100);
    }

    public function getFormattedTechnicianShareAttribute(): string
    {
        return "\u{20B1}".number_format(($this->technician_share_per_visit_centavos ?? 0) / 100);
    }

    public function getFormattedGrossAttribute(): string
    {
        return "\u{20B1}".number_format(($this->gross_per_visit_centavos ?? 0) / 100);
    }

    public function getPlanLabelAttribute(): string
    {
        return str($this->plan)->replace('_', ' ')->title()->toString();
    }
}
