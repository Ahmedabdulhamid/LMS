<?php

namespace App\Http\Controllers;

use App\Services\MuxVideoLifecycle;
use Illuminate\Http\Request;

class MuxWebhookController extends Controller
{
    public function __invoke(Request $request, MuxVideoLifecycle $lifecycle)
    {
        $secret = config('services.mux.webhook_secret');
        abort_unless(is_string($secret) && $secret !== '', 503);
        $parts = [];
        foreach (explode(',', $request->header('Mux-Signature', '')) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) === 2) {
                $parts[$pair[0]][] = $pair[1];
            }
        }
        $timestamp = $parts['t'][0] ?? '';
        abort_unless(ctype_digit($timestamp) && abs(now()->timestamp - (int) $timestamp) <= 300, 401);
        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);
        $valid = false;
        foreach ($parts['v1'] ?? [] as $signature) {
            $valid = hash_equals($expected, $signature) || $valid;
        }
        abort_unless($valid, 401);
        $event = json_decode($request->getContent(), true);
        abort_unless(is_array($event), 400);
        $state = match ($event['type'] ?? '') {
            'video.asset.ready' => 'ready',
            'video.asset.errored' => 'errored',
            default => null,
        };
        if ($state) {
            abort_unless(is_array($event['data'] ?? null), 400);
            $lifecycle->apply(array_replace($event['data'], ['status' => $state]));
        }

        return response()->noContent();
    }
}
