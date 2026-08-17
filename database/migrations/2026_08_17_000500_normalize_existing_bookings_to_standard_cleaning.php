<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $standardServiceId = DB::table('services')->where('slug', 'standard-clean')->value('id');

        if (! $standardServiceId) {
            return;
        }

        DB::table('appointments')->where('service_id', '!=', $standardServiceId)->update([
            'service_id' => $standardServiceId,
            'updated_at' => now(),
        ]);

        DB::table('subscriptions')->where('service_id', '!=', $standardServiceId)->update([
            'service_id' => $standardServiceId,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Previous service selections cannot be reconstructed after normalization.
    }
};
