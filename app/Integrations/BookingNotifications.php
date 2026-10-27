<?php

namespace App\Integrations;

use App\Models\Appointment;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookingNotifications
{
    public function __construct(private ResendEmail $email, private TwilioSms $sms) {}

    public function send(Appointment $appointment): void
    {
        foreach (['email' => $this->email, 'sms' => $this->sms] as $channel => $sender) {
            try {
                $sender->sendBookingConfirmation($appointment);
            } catch (Throwable $error) {
                Log::warning('Booking confirmation could not be sent.', [
                    'appointment_id' => $appointment->id,
                    'channel' => $channel,
                    'error' => $error->getMessage(),
                ]);
            }
        }
    }
}
