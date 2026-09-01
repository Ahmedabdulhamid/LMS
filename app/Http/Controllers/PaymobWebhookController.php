<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaymentWebhook;
use App\Models\Order;
use App\Models\PaymentWebhookEvent;
use App\Services\PaymobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymobWebhookController extends Controller
{
    private const SAFE_TRANSACTION_FIELDS = [
        'id', 'amount_cents', 'currency', 'integration_id', 'success',
        'pending', 'error_occured', 'is_refunded', 'is_voided', 'created_at',
        'is_auth', 'is_capture', 'is_standalone_payment', 'is_live',
    ];

    public function __construct(private readonly PaymobService $paymobService) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $transaction = $request->input('obj');
        $transaction = is_array($transaction) ? $transaction : null;
        $signature = $request->query('hmac') ?? $request->input('hmac') ?? data_get($payload, 'obj.hmac');

        try {
            if (($payload['type'] ?? null) !== 'TRANSACTION' || ! is_array($transaction)) {
                return $this->reject('Unsupported or malformed callback', 422);
            }

            if ($this->paymobService->shouldVerifyHmac()
                && (! is_string($signature) || trim($signature) === ''
                    || ! $this->paymobService->verifyPaymobSignature($payload, $signature))) {
                return $this->reject('Signature verification failed', 401);
            }

            $paymobOrderId = data_get($transaction, 'order.id', $transaction['order'] ?? null);
            $transactionId = $transaction['id'] ?? null;
            if (! is_scalar($paymobOrderId) || ! is_scalar($transactionId)) {
                return $this->reject('Missing required fields', 400);
            }

            $safePayload = [
                'type' => 'TRANSACTION',
                'obj' => Arr::only($transaction, self::SAFE_TRANSACTION_FIELDS) + [
                    'order' => ['id' => (string) $paymobOrderId],
                ],
            ];
            $deduplicationKey = hash('sha256', json_encode([
                'transaction_id' => (string) $transactionId,
                'success' => $transaction['success'] ?? null,
                'pending' => $transaction['pending'] ?? null,
                'is_refunded' => $transaction['is_refunded'] ?? null,
                'is_voided' => $transaction['is_voided'] ?? null,
                'amount_cents' => $transaction['amount_cents'] ?? null,
                'currency' => $transaction['currency'] ?? null,
            ], JSON_THROW_ON_ERROR));

            [$event, $shouldDispatch] = DB::transaction(function () use (
                $deduplicationKey, $transactionId, $paymobOrderId, $safePayload
            ): array {
                $event = PaymentWebhookEvent::query()->firstOrCreate(
                    ['provider' => 'paymob', 'deduplication_key' => $deduplicationKey],
                    [
                        'order_id' => Order::query()->where('paymob_order_id', (string) $paymobOrderId)->value('id'),
                        'event_type' => 'transaction',
                        'provider_transaction_id' => (string) $transactionId,
                        'provider_order_id' => (string) $paymobOrderId,
                        'payload' => $safePayload,
                        'status' => 'received',
                        'received_at' => now(),
                    ],
                );

                if (! $event->wasRecentlyCreated) {
                    $event->increment('delivery_count');
                    $event->refresh();
                }

                $shouldDispatch = $event->wasRecentlyCreated || $event->status === 'failed';
                if ($event->status === 'failed') {
                    $event->update(['status' => 'received', 'next_retry_at' => null]);
                }

                return [$event, $shouldDispatch];
            });

            if ($shouldDispatch) {
                ProcessPaymentWebhook::dispatch($event->id)->afterCommit();
            }

            return response()->json([
                'success' => true,
                'message' => $shouldDispatch ? 'Webhook accepted' : 'Webhook already received',
            ], 202);
        } catch (Throwable $exception) {
            Log::error('Paymob webhook intake failed', [
                'transaction_id' => $transaction['id'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Internal server error'], 500);
        }
    }

    private function reject(string $message, int $status): JsonResponse
    {
        Log::warning('Paymob webhook rejected before persistence', ['reason' => $message]);

        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
