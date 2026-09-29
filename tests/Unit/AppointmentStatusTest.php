<?php

namespace Tests\Unit;

use App\Models\Appointment;
use Tests\TestCase;

class AppointmentStatusTest extends TestCase
{
    public function test_historical_assignment_is_presented_as_confirmed_and_still_blocks_the_slot(): void
    {
        $appointment = new Appointment(['status' => 'assigned']);

        $this->assertSame('Confirmed', $appointment->status_label);
        $this->assertContains('assigned', Appointment::ACTIVE_STATUSES);
        $this->assertFalse($appointment->isFillable('technician_id'));
        $this->assertFalse($appointment->isFillable('latitude'));
    }
}
