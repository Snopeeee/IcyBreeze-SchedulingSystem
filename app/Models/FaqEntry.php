<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaqEntry extends Model
{
    protected $fillable = ['question', 'answer', 'sort_order', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sort_order' => 'integer'];
    }
}
