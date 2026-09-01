<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\PaymobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymobWalletPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_payment_uses_wallet_integration_and_phone_number(): void
    {
        config()->set([
            'paymob.api_key' => 'test-api-key',
            'paymob.iframe_id' => '123',
            'paymob.paymob_integration_card_id' => '111',
            'paymob.paymob_integration_wallet_id' => '222',
        ]);

        Http::fake([
            '*/auth/tokens' => Http::response(['token' => 'auth-token']),
            '*/acceptance/payment_keys' => Http::response(['token' => 'wallet-token']),
            '*/acceptance/payments/pay' => Http::response([
                'redirect_url' => 'https://accept.paymob.com/wallet/confirm',
            ]),
        ]);

        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'currency' => 'EGP',
            'subtotal' => 100,
            'discount_total' => 0,
            'total' => 100,
            'status' => OrderStatus::Pending,
            'paymob_order_id' => '987654',
        ]);

        $url = app(PaymobService::class)->walletPaymentUrl($order, '01012345678');

        $this->assertSame('https://accept.paymob.com/wallet/confirm', $url);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/acceptance/payment_keys')
            && $request['integration_id'] === '222'
        );
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/acceptance/payments/pay')
            && $request['payment_token'] === 'wallet-token'
            && $request['source']['identifier'] === '01012345678'
            && $request['source']['subtype'] === 'WALLET'
        );
    }
}
