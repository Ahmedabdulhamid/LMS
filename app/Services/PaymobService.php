<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymobService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected string $iframeId;

    protected string $integrationCardId;

    protected ?string $integrationWalletId;

    protected ?string $hmacSecret;

    public function __construct()
    {
        $this->baseUrl = config('paymob.base_url');
        $this->apiKey = config('paymob.api_key');
        $this->iframeId = config('paymob.iframe_id');
        $this->integrationCardId = config('paymob.paymob_integration_card_id');
        $this->integrationWalletId = config('paymob.paymob_integration_wallet_id');
        $this->hmacSecret = config('paymob.hmac');
    }

    /**
     * Main function
     * Create Paymob payment and return iframe token
     * Avoids duplicate orders on page refresh
     */
    public function getPaymentToken(Order $order, string $method = 'card'): string
    {
        $this->assertOrderIsPayable($order);
        $integrationId = $this->integrationId($method);

        // Prevent duplicate Paymob orders if already exists
        if ($order->paymob_order_id) {
            $order->update(['payment_status' => 'pending']);
            Log::info('Paymob order already exists', [
                'order_id' => $order->id,
                'paymob_order_id' => $order->paymob_order_id,
            ]);

            // Recreate payment key for existing order
            return $this->recreatePaymentKey($order, $integrationId);
        }

        try {
            // 1- Get Auth Token
            $authToken = $this->getAuthToken();

            // 2- Create Paymob Order
            $paymobOrder = $this->createOrder($authToken, $order);

            if (! isset($paymobOrder['id'])) {
                throw new Exception(
                    'Paymob Order Creation Failed: '.json_encode($paymobOrder)
                );
            }

            // Save Paymob Order ID
            $order->update([
                'paymob_order_id' => $paymobOrder['id'],
                'payment_status' => 'pending',
                'status' => OrderStatus::Pending->value,
            ]);

            Log::info('Paymob order created successfully', [
                'order_id' => $order->id,
                'paymob_order_id' => $paymobOrder['id'],
            ]);

            // 3- Create Payment Key
            return $this->createPaymentKey(
                $authToken,
                $paymobOrder['id'],
                $order,
                $integrationId,
            );
        } catch (Exception $e) {
            Log::error('Paymob payment token generation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Recreate payment key for existing Paymob order
     */
    private function recreatePaymentKey(Order $order, string $integrationId): string
    {
        try {
            $authToken = $this->getAuthToken();

            return $this->createPaymentKey(
                $authToken,
                $order->paymob_order_id,
                $order,
                $integrationId,
            );
        } catch (Exception $e) {
            Log::error('Paymob payment key recreation failed', [
                'order_id' => $order->id,
                'paymob_order_id' => $order->paymob_order_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get Paymob Auth Token
     */
    private function getAuthToken(): string
    {
        try {
            $response = Http::timeout(10)
                ->post(
                    $this->baseUrl.'/auth/tokens',
                    [
                        'api_key' => $this->apiKey,
                    ]
                );

            if (! $response->successful()) {
                Log::error('Paymob auth token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception(
                    'Paymob Auth Failed: '.$response->body()
                );
            }

            return $response->json()['token'];
        } catch (Exception $e) {
            Log::error('Paymob auth token exception', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create Paymob Order
     */
    private function createOrder(
        string $token,
        Order $order
    ): array {
        try {
            $response = Http::timeout(10)
                ->post(
                    $this->baseUrl.'/ecommerce/orders',
                    [
                        'auth_token' => $token,
                        'delivery_needed' => false,
                        'amount_cents' => $this->amountToCents((string) $order->total),
                        'currency' => strtoupper($order->currency),
                        'items' => $order->items
                            ->map(function ($item) {
                                return [
                                    'name' => $item->title,
                                    'amount_cents' => $this->amountToCents((string) $item->total),
                                    'description' => $item->title,
                                    'quantity' => 1,
                                ];
                            })
                            ->toArray(),
                    ]
                );

            if (! $response->successful()) {
                Log::error('Paymob create order request failed', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception(
                    'Paymob Create Order Failed: '.$response->body()
                );
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Paymob create order exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create Payment Key
     */
    private function createPaymentKey(
        string $token,
        int $paymobOrderId,
        Order $order,
        string $integrationId,
    ): string {
        try {
            $response = Http::timeout(10)
                ->post(
                    $this->baseUrl.'/acceptance/payment_keys',
                    [
                        'auth_token' => $token,
                        'amount_cents' => $this->amountToCents((string) $order->total),
                        'expiration' => 3600,
                        'order_id' => $paymobOrderId,
                        'currency' => strtoupper($order->currency),
                        'integration_id' => $integrationId,
                        'billing_data' => [
                            'first_name' => $order->user->name ?? 'Customer',
                            'last_name' => 'User',
                            'email' => $order->user->email ?? 'test@test.com',
                            'phone_number' => '01000000000',
                            'apartment' => 'NA',
                            'floor' => 'NA',
                            'street' => 'NA',
                            'building' => 'NA',
                            'shipping_method' => 'NA',
                            'postal_code' => 'NA',
                            'city' => 'Cairo',
                            'country' => 'EG',
                        ],
                    ]
                );

            if (! $response->successful()) {
                Log::error('Paymob create payment key request failed', [
                    'order_id' => $order->id,
                    'paymob_order_id' => $paymobOrderId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception(
                    'Paymob Payment Key Failed: '.$response->body()
                );
            }

            return $response->json()['token'];
        } catch (Exception $e) {
            Log::error('Paymob create payment key exception', [
                'order_id' => $order->id,
                'paymob_order_id' => $paymobOrderId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**nb
     * Iframe URL
     */
    public function iframeUrl(string $token): string
    {
        return 'https://accept.paymob.com/api/acceptance/iframes/'
            .$this->iframeId
            .'?payment_token='
            .$token;
    }

    public function walletPaymentUrl(Order $order, string $phoneNumber): string
    {
        $paymentToken = $this->getPaymentToken($order, 'wallet');
        $response = Http::timeout(15)->post($this->baseUrl.'/acceptance/payments/pay', [
            'source' => [
                'identifier' => $phoneNumber,
                'subtype' => 'WALLET',
            ],
            'payment_token' => $paymentToken,
        ]);

        if (! $response->successful()) {
            Log::error('Paymob wallet payment request failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new Exception('Paymob Wallet Payment Failed: '.$response->body());
        }

        $redirectUrl = $response->json('redirect_url')
            ?? $response->json('iframe_redirection_url')
            ?? $response->json('data.redirect_url');

        if (! is_string($redirectUrl) || $redirectUrl === '') {
            throw new Exception('Paymob did not return a wallet confirmation URL.');
        }

        return $redirectUrl;
    }

    public function walletIsConfigured(): bool
    {
        return filled($this->integrationWalletId);
    }

    public function expectedAmountCents(Order $order): int
    {
        return $this->amountToCents((string) $order->total);
    }

    public function isAllowedIntegrationId(mixed $integrationId): bool
    {
        if (! is_scalar($integrationId)) {
            return false;
        }

        $allowed = array_filter([
            (string) $this->integrationCardId,
            (string) $this->integrationWalletId,
        ]);

        return in_array((string) $integrationId, $allowed, true);
    }

    private function assertOrderIsPayable(Order $order): void
    {
        $order->refresh();

        if ($order->status !== OrderStatus::Pending || $order->payment_status === 'paid') {
            throw new RuntimeException('This order is not available for payment.');
        }

        if ($this->expectedAmountCents($order) <= 0) {
            throw new RuntimeException('The order total must be greater than zero.');
        }

        if (! preg_match('/\A[A-Z]{3}\z/', strtoupper((string) $order->currency))) {
            throw new RuntimeException('The order currency is invalid.');
        }
    }

    private function amountToCents(string $amount): int
    {
        if (! preg_match('/\A(\d+)(?:\.(\d{1,2}))?\z/', trim($amount), $matches)) {
            throw new RuntimeException('Invalid monetary amount.');
        }

        $fraction = str_pad($matches[2] ?? '', 2, '0');

        return ((int) $matches[1] * 100) + (int) $fraction;
    }

    private function integrationId(string $method): string
    {
        $integrationId = match ($method) {
            'card' => $this->integrationCardId,
            'wallet' => $this->integrationWalletId,
            default => null,
        };

        if (! is_string($integrationId) || $integrationId === '') {
            throw new Exception("Paymob {$method} integration is not configured.");
        }

        return $integrationId;
    }

    /**
     * Verify Paymob HMAC signature
     */
    public function shouldVerifyHmac(): bool
    {
        // A production deployment cannot disable callback authentication.
        return app()->environment('production') || (bool) config('paymob.verify_hmac', true);
    }

    /**
     * Verify a Paymob TRANSACTION processed callback.
     *
     * Paymob signs the concatenated values of these fields in this exact order
     * with the dashboard HMAC secret using HMAC-SHA512.
     */
    public function verifyPaymobSignature(array $payload, string $signature): bool
    {
        if (! is_string($this->hmacSecret) || trim($this->hmacSecret) === '') {
            Log::warning('Paymob signature verification failed', [
                'reason' => 'PAYMOB_HMAC is not configured',
                'hmac_secret_identifier' => 'PAYMOB_HMAC',
            ]);

            return false;
        }

        $transaction = isset($payload['obj']) && is_array($payload['obj'])
            ? $payload['obj']
            : $payload;

        $orderedValues = [
            'amount_cents' => $transaction['amount_cents'] ?? '',
            'created_at' => $transaction['created_at'] ?? '',
            'currency' => $transaction['currency'] ?? '',
            'error_occured' => $transaction['error_occured'] ?? '',
            'has_parent_transaction' => $transaction['has_parent_transaction'] ?? '',
            'id' => $transaction['id'] ?? '',
            'integration_id' => $transaction['integration_id'] ?? '',
            'is_3d_secure' => $transaction['is_3d_secure'] ?? '',
            'is_auth' => $transaction['is_auth'] ?? '',
            'is_capture' => $transaction['is_capture'] ?? '',
            'is_refunded' => $transaction['is_refunded'] ?? '',
            'is_standalone_payment' => $transaction['is_standalone_payment'] ?? '',
            'is_voided' => $transaction['is_voided'] ?? '',
            'order' => is_array($transaction['order'] ?? null)
                ? ($transaction['order']['id'] ?? '')
                : ($transaction['order'] ?? ''),
            'owner' => $transaction['owner'] ?? '',
            'pending' => $transaction['pending'] ?? '',
            'source_data_pan' => $transaction['source_data']['pan'] ?? $transaction['source_data_pan'] ?? '',
            'source_data_sub_type' => $transaction['source_data']['sub_type'] ?? $transaction['source_data_sub_type'] ?? '',
            'source_data_type' => $transaction['source_data']['type'] ?? $transaction['source_data_type'] ?? '',
            'success' => $transaction['success'] ?? '',
        ];

        $normalizedValues = array_map(
            static fn (mixed $value): string => match (true) {
                is_bool($value) => $value ? 'true' : 'false',
                $value === null => '',
                default => (string) $value,
            },
            $orderedValues
        );

        $receivedSignature = strtolower(trim($signature));

        if (! preg_match('/\A[0-9a-f]{128}\z/', $receivedSignature)) {
            Log::warning('Paymob signature verification failed', [
                'reason' => 'The received HMAC is not a valid SHA-512 hex digest',
                'hmac_secret_identifier' => 'PAYMOB_HMAC',
                'transaction_id' => $transaction['id'] ?? null,
            ]);

            return false;
        }

        $generatedSignature = hash_hmac(
            'sha512',
            implode('', $normalizedValues),
            trim($this->hmacSecret)
        );
        $verified = hash_equals($generatedSignature, $receivedSignature);

        if (! $verified) {
            $context = [
                'hmac_secret_identifier' => 'PAYMOB_HMAC',
                'transaction_id' => $transaction['id'] ?? null,
                'paymob_order_id' => $orderedValues['order'],
            ];

            if (app()->environment('local')) {
                $debugValues = $normalizedValues;
                $debugValues['source_data_pan'] = '[REDACTED]';
                $context += [
                    'received_signature' => $receivedSignature,
                    'generated_signature' => $generatedSignature,
                    'ordered_values' => $debugValues,
                ];
            }

            Log::warning('Paymob signature verification failed', $context);
        }

        return $verified;
    }
}
