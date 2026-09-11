<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    @include('partials.application-icons')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Payment status') }} - {{ app(\App\Services\SettingService::class)->name() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f6f7fb 0%, #eef2ff 100%);
            font-family: Arial, sans-serif;
            color: #111827;
        }
        .payment-card {
            width: min(92vw, 640px);
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
            padding: 32px 28px;
        }
        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .brand img {
            width: 46px;
            height: 46px;
            border-radius: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }
        .success {
            background: rgba(34, 197, 94, 0.12);
            color: #166534;
        }
        .pending {
            background: rgba(245, 158, 11, 0.14);
            color: #92400e;
        }
        .failed {
            background: rgba(239, 68, 68, 0.12);
            color: #991b1b;
        }
        .title {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            margin: 0 0 12px;
            text-align: center;
        }
        .message {
            text-align: center;
            color: #4b5563;
            line-height: 1.7;
            margin: 0 0 24px;
        }
        .details {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 24px;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #ececec;
        }
        .details-row:last-child {
            border-bottom: 0;
        }
        .details-label {
            color: #6b7280;
        }
        .details-value {
            font-weight: 700;
            color: #111827;
        }
        .actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.2s ease;
        }
        .btn-primary {
            background: #111827;
            color: #ffffff;
        }
        .btn-secondary {
            background: #eef2ff;
            color: #1f2937;
        }
        @media (max-width: 640px) {
            .payment-card {
                padding: 22px 18px;
            }
            .details-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <main class="payment-card">
        <div class="brand">
            <img src="{{ app(\App\Services\SettingService::class)->logoUrl() }}" alt="{{ app(\App\Services\SettingService::class)->name() }}">
            <strong>{{ app(\App\Services\SettingService::class)->name() }}</strong>
        </div>

        @php
            $success = filter_var(request('success', false), FILTER_VALIDATE_BOOLEAN);
            $paymentStatus = $success ? 'success' : 'pending';
            $message = $success
                ? 'Your payment was approved successfully. We are finishing your order setup.'
                : 'Your payment request is being processed. Please wait a moment while we confirm the transaction.';
        @endphp

        <div class="status-badge {{ $paymentStatus }}">
            {{ $success ? 'Paid' : 'Processing' }}
        </div>

        <h1 class="title">{{ $success ? 'Payment successful' : 'Payment in progress' }}</h1>
        <p class="message">{{ $message }}</p>

        <div class="details">
            <div class="details-row">
                <span class="details-label">Order</span>
                <span class="details-value">#{{ $order->number ?? $order->id ?? request('order_id') ?? 'N/A' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Amount</span>
                <span class="details-value">{{ number_format((float) ($order->total ?? 0), 2) }} EGP</span>
            </div>
            <div class="details-row">
                <span class="details-label">Transaction ID</span>
                <span class="details-value">{{ request('id') ?? $paymobOrderId ?? 'N/A' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Status</span>
                <span class="details-value">{{ $success ? 'Approved' : 'Pending confirmation' }}</span>
            </div>
        </div>

        <div class="actions">
            <a href="{{ route('home') }}" class="btn btn-primary">Back to Home</a>
            @if($order)
                <a href="{{ route('checkout.orders.show', ['order' => $order->number]) }}" class="btn btn-secondary">View Order</a>
            @endif
        </div>
    </main>
</body>
</html>
