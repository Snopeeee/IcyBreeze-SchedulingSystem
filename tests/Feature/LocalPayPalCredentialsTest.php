<?php

namespace Tests\Feature;

use App\Integrations\LocalPayPalCredentials;
use App\Integrations\PayPalCheckout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LocalPayPalCredentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sandbox_credentials_are_encrypted_and_local_only(): void
    {
        $this->app['env'] = 'local';

        try {
            $store = app(LocalPayPalCredentials::class);
            $store->save(str_repeat('i', 80), str_repeat('s', 80));

            $ciphertext = DB::table('local_integration_credentials')
                ->where('provider', 'paypal_sandbox')->value('encrypted_credentials');

            $this->assertIsString($ciphertext);
            $this->assertStringNotContainsString(str_repeat('s', 80), $ciphertext);
            $this->assertSame(str_repeat('s', 80), $store->read()['client_secret']);
            $this->assertTrue(app(PayPalCheckout::class)->available());

            $this->app['env'] = 'production';
            $this->assertNull($store->read());
            $this->assertFalse(app(PayPalCheckout::class)->available());
        } finally {
            $this->app['env'] = 'testing';
        }
    }
}
