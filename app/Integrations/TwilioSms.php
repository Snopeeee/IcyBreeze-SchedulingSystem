<?php

namespace App\Integrations;

use App\Models\Appointment;
use Illuminate\Support\Facades\Http;

class TwilioSms
{
    public function available(): bool
    {
        return filled(config('integrations.twilio.account_sid'))
            && filled(config('integrations.twilio.auth_token'))
            && filled(config('integrations.twilio.from'));
    }

    public function sendBookingConfirmation(Appointment $appointment): void
    {
        if (! $this->available()) {
            return;
        }

        $appointment->loadMissing('customer');
        $phone = preg_replace('/\D+/', '', $appointment->customer->phone);
        $to = preg_match('/^09\d{9}$/', $phone) ? '+63'.substr($phone, 1) : null;
        if ($to === null && preg_match('/^63\d{10}$/', $phone)) {
            $to = '+'.$phone;
        }
        if ($to === null) {
            return;
        }

        $sid = config('integrations.twilio.account_sid');
        Http::asForm()->withBasicAuth($sid, config('integrations.twilio.auth_token'))
            ->timeout(8)->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $to,
                'From' => config('integrations.twilio.from'),
                'Body' => 'IcyBreeze: Your cleaning appointment '.$appointment->reference.' is confirmed for '
                    .$appointment->starts_at->format('M j, g:i A').'. Manage: '.route('booking.manage', $appointment->manage_token),
            ])->throw();
    }
}
