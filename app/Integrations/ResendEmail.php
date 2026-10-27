<?php

namespace App\Integrations;

use App\Models\Appointment;
use Illuminate\Support\Facades\Http;

class ResendEmail
{
    public function available(): bool
    {
        return filled(config('integrations.resend.api_key')) && filled(config('integrations.resend.from'));
    }

    public function sendBookingConfirmation(Appointment $appointment): void
    {
        if (! $this->available()) {
            return;
        }

        $appointment->loadMissing('customer');
        $name = e($appointment->customer->first_name);
        $reference = e($appointment->reference);
        $date = e($appointment->starts_at->format('F j, Y \a\t g:i A'));
        $url = e(route('booking.manage', $appointment->manage_token));

        Http::acceptJson()->withToken(config('integrations.resend.api_key'))
            ->timeout(8)->post('https://api.resend.com/emails', [
                'from' => config('integrations.resend.from'),
                'to' => [$appointment->customer->email],
                'subject' => 'IcyBreeze booking confirmed · '.$appointment->reference,
                'html' => "<p>Hello {$name},</p><p>Your aircon cleaning appointment <strong>{$reference}</strong> is confirmed for {$date}.</p><p><a href=\"{$url}\">View or manage your appointment</a></p><p>IcyBreeze Aircon Cleaning</p>",
            ])->throw();
    }
}
