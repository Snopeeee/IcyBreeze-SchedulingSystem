<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aircon_unit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('price_centavos');
            $table->unsignedInteger('technician_share_centavos');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('aircon_unit_types')->insert([
            ['name' => 'Window Type', 'slug' => 'window-type', 'price_centavos' => 70000, 'technician_share_centavos' => 40000, 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Window Type Inverter', 'slug' => 'window-type-inverter', 'price_centavos' => 75000, 'technician_share_centavos' => 45000, 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Split Type', 'slug' => 'split-type', 'price_centavos' => 95000, 'technician_share_centavos' => 55000, 'is_active' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Split Type Inverter', 'slug' => 'split-type-inverter', 'price_centavos' => 100000, 'technician_share_centavos' => 60000, 'is_active' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('aircon_unit_type_id')->nullable()->after('service_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('unit_price_centavos')->nullable()->after('quantity');
            $table->unsignedInteger('technician_share_centavos')->nullable()->after('total_centavos');
            $table->unsignedInteger('gross_centavos')->nullable()->after('technician_share_centavos');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('aircon_unit_type_id')->nullable()->after('service_id')->constrained()->nullOnDelete();
            $table->string('unit_type')->nullable()->after('plan');
            $table->unsignedInteger('unit_price_centavos')->nullable()->after('quantity');
            $table->unsignedInteger('technician_share_per_visit_centavos')->nullable()->after('price_per_visit_centavos');
            $table->unsignedInteger('gross_per_visit_centavos')->nullable()->after('technician_share_per_visit_centavos');
        });

        $rates = DB::table('aircon_unit_types')->get()->keyBy('slug');

        foreach ($rates as $slug => $rate) {
            $name = $rate->name;

            DB::table('appointments')
                ->where('unit_type', $name)
                ->update([
                    'aircon_unit_type_id' => $rate->id,
                    'unit_price_centavos' => DB::raw('CASE WHEN quantity > 0 THEN subtotal_centavos / quantity ELSE subtotal_centavos END'),
                    'technician_share_centavos' => DB::raw($rate->technician_share_centavos.' * quantity'),
                    'gross_centavos' => DB::raw('CASE WHEN total_centavos > ('.$rate->technician_share_centavos.' * quantity) THEN total_centavos - ('.$rate->technician_share_centavos.' * quantity) ELSE 0 END'),
                ]);
        }

        $splitType = $rates->get('split-type');
        if ($splitType) {
            DB::table('subscriptions')->whereNull('aircon_unit_type_id')->update([
                'aircon_unit_type_id' => $splitType->id,
                'unit_type' => $splitType->name,
                'unit_price_centavos' => $splitType->price_centavos,
                'technician_share_per_visit_centavos' => DB::raw($splitType->technician_share_centavos.' * quantity'),
                'gross_per_visit_centavos' => DB::raw('CASE WHEN price_per_visit_centavos > ('.$splitType->technician_share_centavos.' * quantity) THEN price_per_visit_centavos - ('.$splitType->technician_share_centavos.' * quantity) ELSE 0 END'),
            ]);

            DB::table('appointments')->where('reference', 'like', 'ICY-DEMO-%')->update([
                'aircon_unit_type_id' => $splitType->id,
                'unit_type' => $splitType->name,
                'unit_price_centavos' => $splitType->price_centavos,
                'subtotal_centavos' => DB::raw($splitType->price_centavos.' * quantity'),
                'total_centavos' => DB::raw($splitType->price_centavos.' * quantity'),
                'technician_share_centavos' => DB::raw($splitType->technician_share_centavos.' * quantity'),
                'gross_centavos' => DB::raw(($splitType->price_centavos - $splitType->technician_share_centavos).' * quantity'),
            ]);

            DB::table('payments')->whereIn('appointment_id', function ($query) {
                $query->select('id')->from('appointments')->where('reference', 'like', 'ICY-DEMO-%');
            })->update([
                'amount_centavos' => DB::raw('(SELECT total_centavos FROM appointments WHERE appointments.id = payments.appointment_id)'),
            ]);

            DB::table('subscriptions')->where('reference', 'like', 'SUB-DEMO-%')->update([
                'unit_price_centavos' => $splitType->price_centavos,
                'price_per_visit_centavos' => 85500,
                'technician_share_per_visit_centavos' => $splitType->technician_share_centavos,
                'gross_per_visit_centavos' => 30500,
            ]);
        }

        DB::table('services')->where('slug', 'standard-clean')->update([
            'name' => 'Standard Cleaning',
            'short_description' => 'Professional standard cleaning priced by aircon unit type.',
            'description' => 'One careful standard-cleaning service for window and split-type air conditioners, including cleaning, drainage inspection, and an operational cooling check.',
            'price_centavos' => 70000,
            'is_active' => true,
            'sort_order' => 1,
            'updated_at' => $now,
        ]);

        DB::table('services')->where('slug', '!=', 'standard-clean')->update([
            'is_active' => false,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['aircon_unit_type_id']);
            $table->dropColumn([
                'aircon_unit_type_id', 'unit_type', 'unit_price_centavos',
                'technician_share_per_visit_centavos', 'gross_per_visit_centavos',
            ]);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['aircon_unit_type_id']);
            $table->dropColumn([
                'aircon_unit_type_id', 'unit_price_centavos',
                'technician_share_centavos', 'gross_centavos',
            ]);
        });

        Schema::dropIfExists('aircon_unit_types');
    }
};
