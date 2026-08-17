<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Services\RouteOptimizer;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class RouteOptimizerTest extends TestCase
{
    public function test_it_preserves_appointment_times_before_optimizing_distance(): void
    {
        $day = CarbonImmutable::parse('2026-08-17 00:00:00');
        $appointments = collect([
            new Appointment(['reference' => 'AFTERNOON', 'starts_at' => $day->setHour(15), 'latitude' => 8.2149, 'longitude' => 124.2358]),
            new Appointment(['reference' => 'MORNING', 'starts_at' => $day->setHour(8), 'latitude' => 8.2286, 'longitude' => 124.2449]),
            new Appointment(['reference' => 'MIDDAY', 'starts_at' => $day->setHour(10), 'latitude' => 8.2446, 'longitude' => 124.2595]),
        ]);

        $result = (new RouteOptimizer)->optimize($appointments, 8.2280, 124.2452);

        $this->assertSame(['MORNING', 'MIDDAY', 'AFTERNOON'], $result->pluck('reference')->all());
    }
}
