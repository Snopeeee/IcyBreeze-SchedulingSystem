<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id', 'method', 'provider', 'reference', 'status',
        'amount_centavos', 'currency', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_centavos' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return "\u{20B1}".number_format($this->amount_centavos / 100);
    }
}
