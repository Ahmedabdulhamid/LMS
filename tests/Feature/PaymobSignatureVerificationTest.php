<?php

namespace Tests\Feature;

use App\Services\PaymobService;
use Tests\TestCase;

class PaymobSignatureVerificationTest extends TestCase
{
    public function test_it_uses_the_hmac_secret_from_config_when_verifying_webhooks(): void
    {
        config([
            'paymob.hmac' => 'webhook-secret-123',
            'paymob.secret_key' => 'legacy-secret-456',
        ]);

        $payload = ['obj' => $this->transactionPayload()];

        $signature = hash_hmac('sha512', $this->signedString(), 'webhook-secret-123');

        $service = app(PaymobService::class);

        $this->assertTrue($service->verifyPaymobSignature($payload, $signature));
        $this->assertTrue($service->verifyPaymobSignature($payload, strtoupper($signature)));
    }

    public function test_it_does_not_fall_back_to_the_api_secret_key(): void
    {
        config([
            'paymob.secret_key' => 'sandbox-secret',
            'paymob.hmac' => null,
        ]);

        $payload = ['type' => 'TRANSACTION', 'obj' => $this->transactionPayload()];
        $signature = hash_hmac('sha512', $this->signedString(), 'sandbox-secret');

        $service = app(PaymobService::class);

        $this->assertFalse($service->verifyPaymobSignature($payload, $signature));
    }

    private function transactionPayload(): array
    {
        return [
            'amount_cents' => 12345,
            'created_at' => '2026-08-25T10:00:00.000000',
            'currency' => 'EGP',
            'error_occured' => false,
            'has_parent_transaction' => false,
            'id' => 521841030,
            'integration_id' => 5044737,
            'is_3d_secure' => true,
            'is_auth' => false,
            'is_capture' => false,
            'is_refunded' => false,
            'is_standalone_payment' => true,
            'is_voided' => false,
            'order' => ['id' => 42],
            'owner' => 1947043,
            'pending' => false,
            'source_data' => [
                'pan' => '2346',
                'sub_type' => 'MasterCard',
                'type' => 'card',
            ],
            'success' => true,
        ];
    }

    private function signedString(): string
    {
        return '123452026-08-25T10:00:00.000000EGPfalsefalse5218410305044737'
            . 'truefalsefalsefalsetruefalse421947043false2346MasterCardcardtrue';
    }
}
