<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')
            ->where('slug', 'standard-clean')
            ->update([
                'name' => 'Standard Cleaning',
                'short_description' => 'Professional standard cleaning priced by aircon unit type.',
                'description' => 'One careful standard-cleaning service for window and split-type air conditioners, including cleaning, drainage inspection, and an operational cooling check.',
                'price_centavos' => 70000,
                'duration_minutes' => 75,
                'buffer_minutes' => 30,
                'is_active' => true,
                'sort_order' => 1,
                'updated_at' => now(),
            ]);

        DB::table('services')
            ->where('slug', '!=', 'standard-clean')
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('services')
            ->whereIn('slug', ['deep-clean', 'window-type-clean'])
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);

        DB::table('services')
            ->where('slug', 'standard-clean')
            ->update([
                'name' => 'Standard Clean',
                'short_description' => 'Routine cleaning for fresher air and efficient cooling.',
                'description' => 'Filter, cover, coil surface, drain line, and operational check for regularly maintained aircon units.',
                'updated_at' => now(),
            ]);
    }
};
