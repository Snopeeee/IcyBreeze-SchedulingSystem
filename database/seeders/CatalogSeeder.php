<?php

namespace Database\Seeders;

use App\Models\AirconUnitType;
use App\Models\Service;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        Service::where('slug', '!=', Service::STANDARD_SLUG)->update(['is_active' => false]);

        Service::updateOrCreate(['slug' => Service::STANDARD_SLUG], [
            'name' => 'Standard Cleaning',
            'short_description' => 'Professional standard cleaning priced by aircon unit type.',
            'description' => 'One careful standard-cleaning service for window and split-type air conditioners, including cleaning, drainage inspection, and an operational cooling check.',
            'price_centavos' => 70000,
            'duration_minutes' => 75,
            'buffer_minutes' => 30,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $unitTypes = [
            ['name' => 'Window Type', 'slug' => 'window-type', 'price_centavos' => 70000, 'technician_share_centavos' => 40000, 'sort_order' => 1],
            ['name' => 'Window Type Inverter', 'slug' => 'window-type-inverter', 'price_centavos' => 75000, 'technician_share_centavos' => 45000, 'sort_order' => 2],
            ['name' => 'Split Type', 'slug' => 'split-type', 'price_centavos' => 95000, 'technician_share_centavos' => 55000, 'sort_order' => 3],
            ['name' => 'Split Type Inverter', 'slug' => 'split-type-inverter', 'price_centavos' => 100000, 'technician_share_centavos' => 60000, 'sort_order' => 4],
        ];

        AirconUnitType::whereNotIn('slug', collect($unitTypes)->pluck('slug'))->update(['is_active' => false]);

        foreach ($unitTypes as $unitType) {
            AirconUnitType::updateOrCreate(
                ['slug' => $unitType['slug']],
                $unitType + ['is_active' => true],
            );
        }
    }
}
