<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve service IDs, slugs, prices, and booking relationships.
        DB::table('services')->where('slug', 'standard-clean')->get()->each(function ($service) {
            $changes = [];
            foreach (['name', 'short_description', 'description'] as $field) {
                $updated = preg_replace('/\bstandard[- ](?:aircon[- ])?clean(?:ing)?\b/i', 'Aircon Cleaning', $service->$field);
                if ($updated !== $service->$field) {
                    $changes[$field] = $updated;
                }
            }
            if ($changes) {
                DB::table('services')->where('id', $service->id)->update($changes);
            }
        });
    }

    public function down(): void
    {
        // Keep copy changes on rollback to avoid overwriting later admin edits.
    }
};
