<?php

return [
    'api_key' => env('PAYMOB_API_KEY'),
    'iframe_id' => env('PAYMOB_IFRAME_ID'),
    'hmac' => env('PAYMOB_HMAC'),
    'secret_key' => env('PAYMOB_SECRET_KEY'),
    'base_url' => env('PAYMOB_BASE_URL', 'https://accept.paymob.com/api'),
    'paymob_integration_wallet_id' => env('PAYMOB_INTEGRATION_WALLET_ID'),
    'paymob_integration_card_id' => env('PAYMOB_INTEGRATION_CARD_ID'),
    'verify_hmac' => env('PAYMOB_VERIFY_HMAC', true),
    'alert_email' => env('PAYMENT_ALERT_EMAIL'),
    'stale_pending_minutes' => (int) env('PAYMENT_STALE_PENDING_MINUTES', 30),
];
