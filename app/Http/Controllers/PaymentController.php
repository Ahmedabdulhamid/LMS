<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Show payment callback page
     * This endpoint handles the redirect from Paymob after payment attempt
     * It only shows payment status - does NOT mark payment as successful
     * The webhook is the only source of truth for payment status
     */
    public function callback(Request $request): View|RedirectResponse
    {
        $orderId = $request->input('order') ?? session('pending_payment_order_id');


        if (! $orderId) {
            $student = Auth::guard('student')->user();
            $latestOrder = $student?->orders()->latest()->first();

            if ($latestOrder) {
                return redirect()->route('checkout.orders.show', ['order' => $latestOrder->number]);
            }

            return redirect()->route('home')->with('error', 'Payment callback did not include an order reference.');
        }

        $order = Order::where('paymob_order_id', $orderId)->first();

        abort_if(! $order, 404, 'Order not found for Paymob callback.');
        abort_unless((int) $order->user_id === (int) Auth::guard('student')->id(), 403);

        $callbackFailed = $request->has('success')
            && ! filter_var($request->input('success'), FILTER_VALIDATE_BOOLEAN);
        $paymentState = $this->state($order);

        return view('payment.callback', [
            'order' => $order,
            'paymobOrderId' => $request->input('order'),
            'transactionId' => $request->input('id'),
            'paymentState' => $callbackFailed && $paymentState === 'processing' ? 'failed' : $paymentState,
        ]);
    }

    /** Return the authoritative state while the webhook job is running. */
    public function status(Order $order): JsonResponse
    {
        abort_unless((int) $order->user_id === (int) Auth::guard('student')->id(), 403);

        return response()->json(['state' => $this->state($order->fresh())]);
    }

    private function state(Order $order): string
    {
        return match ($order->payment_status) {
            'paid', 'completed' => 'success',
            'failed', 'refunded', 'cancelled' => 'failed',
            default => 'processing',
        };
    }
}
