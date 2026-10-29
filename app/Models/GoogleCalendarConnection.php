<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleCalendarConnection extends Model
{
    protected $fillable = ['user_id', 'refresh_token'];

    protected $hidden = ['refresh_token'];

    protected function casts(): array
    {
        return ['refresh_token' => 'encrypted'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
