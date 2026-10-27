<?php

namespace App\Integrations;

use App\Models\Appointment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PayPalCheckout
{
    public function available(): bool
    {
        return (bool) config('integrations.paypal.enabled')
            && in_array(config('integrations.paypal.mode'), ['sandbox', 'live'], true)
            && (! app()->environment('production') || config('integrations.paypal.mode') === 'live')
            && filled(config('integrations.paypal.client_id'))
            && filled(config('integrations.paypal.client_secret'));
    }

    public function createOrder(Appointment $appointment): array
    {
        $response = Http::acceptJson()->withToken($this->accessToken())
            ->withHeaders([
                'PayPal-Request-Id' => (string) Str::uuid(),
                'Prefer' => 'return=representation',
            ])
            ->timeout(12)
            ->post($this->baseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $appointment->reference,
                    'custom_id' => $appointment->reference,
                    'description' => 'IcyBreeze Aircon Cleaning',
                    'amount' => [
                        'currency_code' => 'PHP',
                        'value' => $this->amount($appointment->total_centavos),
                    ],
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'return_url' => route('paypal.return', $appointment->manage_token),
                            'cancel_url' => route('paypal.cancel', $appointment->manage_token),
                            'user_action' => 'PAY_NOW',
                            'shipping_preference' => 'NO_SHIPPING',
                        ],
                    ],
                ],
            ])->throw()->json();

        $orderId = data_get($response, 'id');
        $approvalUrl = $this->approvalUrl($response);
        if (! is_string($orderId) || $orderId === '' || $approvalUrl === null) {
            throw new RuntimeException('PayPal did not return an order and approval URL.');
        }

        return ['id' => $orderId, 'approval_url' => $approvalUrl];
    }

    public function approvalUrlFor(string $orderId): ?string
    {
        return $this->approvalUrl($this->orderDetails($orderId));
    }

    public function orderDetails(string $orderId): array
    {
        return Http::acceptJson()->withToken($this->accessToken())
            ->timeout(10)->get($this->baseUrl().'/v2/checkout/orders/'.rawurlencode($orderId))
            ->throw()->json();
    }

    public function captureOrder(Appointment $appointment, string $orderId): array
    {
        $response = Http::acceptJson()->withToken($this->accessToken())
            ->withHeaders([
                'PayPal-Request-Id' => 'icy-cap-'.substr(hash('sha256', $orderId), 0, 28),
                'Prefer' => 'return=representation',
            ])
            ->timeout(12)
            ->withBody('{}', 'application/json')
            ->post($this->baseUrl().'/v2/checkout/orders/'.rawurlencode($orderId).'/capture')
            ->throw()->json();

        $this->validateCapturedOrder($appointment, $orderId, $response);

        return $response;
    }

    public function verifyCapturedOrder(Appointment $appointment, string $orderId): void
    {
        $this->validateCapturedOrder($appointment, $orderId, $this->orderDetails($orderId));
    }

    private function validateCapturedOrder(Appointment $appointment, string $orderId, array $response): void
    {
        $capture = data_get($response, 'purchase_units.0.payments.captures.0');
        $reference = data_get($response, 'purchase_units.0.custom_id');
        if (data_get($response, 'id') !== $orderId
            || data_get($response, 'status') !== 'COMPLETED'
            || $reference !== $appointment->reference
            || data_get($capture, 'status') !== 'COMPLETED'
            || data_get($capture, 'amount.currency_code') !== 'PHP'
            || data_get($capture, 'amount.value') !== $this->amount($appointment->total_centavos)
            || count(data_get($response, 'purchase_units', [])) !== 1
            || count(data_get($response, 'purchase_units.0.payments.captures', [])) !== 1) {
            throw new RuntimeException('PayPal capture did not match the appointment amount and currency.');
        }
    }

    private function accessToken(): string
    {
        if (! $this->available()) {
            throw new RuntimeException('PayPal is not configured.');
        }

        $token = Http::asForm()->withBasicAuth(
            config('integrations.paypal.client_id'),
            config('integrations.paypal.client_secret'),
        )->timeout(10)->post($this->baseUrl().'/v1/oauth2/token', [
            'grant_type' => 'client_credentials',
        ])->throw()->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('PayPal did not return an access token.');
        }

        return $token;
    }

    private function approvalUrl(array $order): ?string
    {
        foreach ($order['links'] ?? [] as $link) {
            if (in_array($link['rel'] ?? null, ['payer-action', 'approve'], true)) {
                $url = $link['href'] ?? null;
                $host = is_string($url) ? parse_url($url, PHP_URL_HOST) : null;
                if (is_string($host) && ($host === 'paypal.com' || str_ends_with($host, '.paypal.com')) && str_starts_with($url, 'https://')) {
                    return $url;
                }
            }
        }

        return null;
    }

    private function baseUrl(): string
    {
        return config('integrations.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function amount(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }
}
