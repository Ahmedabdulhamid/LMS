<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\SubscriptionPlan;
use App\Services\PaymobService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CheckoutOrder extends Component
{
    public ?string $paymentToken = null;

    public ?string $iframeUrl = null;

    public string $paymentMethod = 'card';

    public string $walletPhone = '';

    public bool $walletAvailable = false;

    public Order $order;

    public function mount(Order $order, PaymobService $paymobService): void
    {
        abort_unless((int) $order->user_id === (int) Auth::guard('student')->id(), 404);

        $this->order = $order->load('items.purchasable');
        $this->walletAvailable = $paymobService->walletIsConfigured();
        $this->order->items->loadMorph('purchasable', [
            SubscriptionPlan::class => ['courses'],
        ]);

        try {
            // Generate payment token only when needed
            $this->paymentToken = $paymobService->getPaymentToken($this->order);

            // Generate iframe URL
            $this->iframeUrl = $paymobService->iframeUrl($this->paymentToken);
        } catch (\Exception $e) {
            // Log error and show user-friendly message
            session()->flash('error', 'Failed to initialize payment. Please try again.');
            redirect()->back();
        }
    }

    public function payWithWallet(PaymobService $paymobService): void
    {
        abort_unless((int) $this->order->user_id === (int) Auth::guard('student')->id(), 404);

        if (! $paymobService->walletIsConfigured()) {
            $this->addError('walletPhone', 'Mobile wallet payments are not configured.');

            return;
        }

        $validated = $this->validate([
            'walletPhone' => ['required', 'regex:/^01[0125][0-9]{8}$/'],
        ], [
            'walletPhone.required' => 'Enter the mobile wallet phone number.',
            'walletPhone.regex' => 'Enter a valid Egyptian mobile number.',
        ]);

        try {
            $redirectUrl = $paymobService->walletPaymentUrl($this->order, $validated['walletPhone']);
            $this->redirect($redirectUrl);
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('walletPhone', 'Unable to start the wallet payment. Please try again.');
        }
    }

    public function render(): View
    {
        return view('livewire.checkout-order')->layout('layouts.course-public');
    }
}
