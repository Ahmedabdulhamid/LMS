<?php

use App\Http\Controllers\PaymobWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/paymob/webhook', [PaymobWebhookController::class, 'handle'])
    ->name('paymob.webhook')
    ->withoutMiddleware(['throttle']);
