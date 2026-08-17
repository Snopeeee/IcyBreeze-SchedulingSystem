<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory;

    public const ACTIVE_STATUSES = ['pending_payment', 'pending_confirmation', 'confirmed', 'assigned', 'in_progress'];

    protected $fillable = [
        'reference', 'manage_token', 'customer_id', 'service_id', 'aircon_unit_type_id', 'technician_id', 'unit_type', 'quantity',
        'unit_price_centavos',
        'starts_at', 'ends_at', 'status', 'payment_status', 'source', 'subtotal_centavos',
        'travel_fee_centavos', 'total_centavos', 'technician_share_centavos', 'gross_centavos', 'address_line', 'barangay', 'city',
        'province', 'postal_code', 'landmark', 'latitude', 'longitude', 'location_accuracy_meters',
        'location_consent_at', 'customer_notes', 'internal_notes',
        'confirmed_at', 'completed_at', 'cancelled_at', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'location_consent_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'location_accuracy_meters' => 'decimal:2',
            'quantity' => 'integer',
            'subtotal_centavos' => 'integer',
            'travel_fee_centavos' => 'integer',
            'total_centavos' => 'integer',
            'unit_price_centavos' => 'integer',
            'technician_share_centavos' => 'integer',
            'gross_centavos' => 'integer',
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

    public function getHasGpsAttribute(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function getMapsUrlAttribute(): ?string
    {
        if (! $this->has_gps) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.$this->latitude.','.$this->longitude;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(AppointmentStatusHistory::class)->latest();
    }

    public function getFormattedTotalAttribute(): string
    {
        return "\u{20B1}".number_format($this->total_centavos / 100);
    }

    public function getFormattedTechnicianShareAttribute(): string
    {
        return "\u{20B1}".number_format(($this->technician_share_centavos ?? 0) / 100);
    }

    public function getFormattedGrossAttribute(): string
    {
        return "\u{20B1}".number_format(($this->gross_centavos ?? 0) / 100);
    }

    public function getStatusLabelAttribute(): string
    {
        return str($this->status)->replace('_', ' ')->title()->toString();
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return str($this->payment_status)->replace('_', ' ')->title()->toString();
    }
}
