<main class="co-page">
    <section class="co-hero">
        <div class="cp-container co-hero-inner">
            <div>
                <span class="co-eyebrow">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.6 2.9 8.8 7 10 4.1-1.2 7-5.4 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    {{ __('lms.checkout.eyebrow') }}
                </span>
                <h1>{{ __('lms.checkout.title') }}</h1>
                <p>{{ __('lms.checkout.order_number', ['number' => $order->number]) }}</p>
            </div>
            <div class="co-steps" aria-label="{{ __('lms.checkout.progress') }}">
                <span class="done"><i>✓</i>{{ __('lms.checkout.review_step') }}</span><b></b>
                <span class="active"><i>2</i>{{ __('lms.checkout.payment_step') }}</span><b></b>
                <span><i>3</i>{{ __('lms.checkout.done_step') }}</span>
            </div>
        </div>
    </section>

    <section class="cp-container co-layout">
        <div class="co-main">
            <article class="co-card">
                <header>
                    <div>
                        <span>{{ $order->items->first()?->purchasable instanceof \App\Models\SubscriptionPlan ? __('lms.checkout.subscription') : __('lms.checkout.course') }}</span>
                        <h2>{{ __('lms.checkout.order_details') }}</h2>
                    </div>
                    <b><i></i>{{ __('lms.checkout.pending') }}</b>
                </header>
                @foreach($order->items as $item)
                    <div class="co-item">
                        <div class="co-item-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5a2.5 2.5 0 0 0 0 5H20"/><path d="M4 5.5V20.5M8 7h8"/></svg></div>
                        <div>
                            <h3>{{ $item->title }}</h3>
                            @if($item->purchasable instanceof \App\Models\SubscriptionPlan)
                                <p>{{ __('lms.checkout.subscription_notice') }}</p>
                                @if($item->purchasable->courses->isNotEmpty())
                                    <ul class="co-included-courses">
                                        @foreach($item->purchasable->courses as $course)
                                            <li><span>✓</span>{{ $course->title }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            @else
                                <p>{{ __('lms.checkout.snapshot_notice') }}</p>
                            @endif
                        </div>
                        <strong>{{ number_format((float) $item->total, 2) }} <small>{{ $order->currency }}</small></strong>
                    </div>
                @endforeach
            </article>

            <article class="co-payment">
                <header>
                    <div class="co-payment-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19M6 15h4"/></svg></div>
                    <div>
                        <span>{{ __('lms.checkout.secure_payment') }}</span>
                        <h2>{{ __('lms.checkout.payment_details') }}</h2>
                        <p>{{ __('lms.checkout.payment_description') }}</p>
                    </div>
                    <div class="co-secure-badge"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>SSL</div>
                </header>
                <div class="co-payment-methods">
                    <button type="button" wire:click="$set('paymentMethod', 'card')" @class(['active' => $paymentMethod === 'card'])>
                        <span>💳</span><b>{{ app()->isLocale('ar') ? 'بطاقة بنكية' : 'Card' }}</b>
                    </button>
                    <button type="button" wire:click="$set('paymentMethod', 'wallet')" @class(['active' => $paymentMethod === 'wallet']) @disabled(! $walletAvailable)>
                        <span>📱</span><b>{{ app()->isLocale('ar') ? 'محفظة إلكترونية' : 'Mobile wallet' }}</b>
                    </button>
                </div>

                @if($paymentMethod === 'card')
                    <div class="co-frame-wrap" wire:key="card-payment">
                        <div class="co-frame-loading" aria-hidden="true"><span></span><p>{{ __('lms.checkout.loading_payment') }}</p></div>
                        <iframe src="{{ $iframeUrl }}" title="{{ __('lms.checkout.payment_details') }}" loading="eager" allow="payment" referrerpolicy="strict-origin-when-cross-origin" onload="this.parentElement.classList.add('is-loaded')"></iframe>
                    </div>
                @else
                    <form wire:submit="payWithWallet" class="co-wallet-form" wire:key="wallet-payment">
                        <div class="co-wallet-icon">📱</div>
                        <h3>{{ app()->isLocale('ar') ? 'الدفع بالمحفظة الإلكترونية' : 'Pay with mobile wallet' }}</h3>
                        <p>{{ app()->isLocale('ar') ? 'أدخل رقم الهاتف المرتبط بمحفظة Vodafone Cash أو Orange Cash أو e& money أو WE Pay.' : 'Enter the phone number linked to Vodafone Cash, Orange Cash, e& money, or WE Pay.' }}</p>
                        <label for="wallet-phone">{{ app()->isLocale('ar') ? 'رقم المحفظة' : 'Wallet phone number' }}</label>
                        <input id="wallet-phone" type="tel" inputmode="numeric" maxlength="11" wire:model="walletPhone" placeholder="01xxxxxxxxx" autocomplete="tel">
                        @error('walletPhone')<small>{{ $message }}</small>@enderror
                        <button type="submit" wire:loading.attr="disabled" wire:target="payWithWallet">
                            <span wire:loading.remove wire:target="payWithWallet">{{ app()->isLocale('ar') ? 'المتابعة للدفع' : 'Continue to payment' }}</span>
                            <span wire:loading wire:target="payWithWallet">{{ app()->isLocale('ar') ? 'جاري التحويل…' : 'Redirecting…' }}</span>
                        </button>
                    </form>
                @endif
            </article>
        </div>

        <aside class="co-summary">
            <h2>{{ __('lms.checkout.summary') }}</h2>
            <dl>
                <div><dt>{{ __('lms.checkout.original_price') }}</dt><dd>{{ number_format((float) $order->subtotal, 2) }} {{ $order->currency }}</dd></div>
                @if((float) $order->discount_total > 0)
                    <div><dt>{{ __('lms.checkout.discount') }}</dt><dd class="discount">−{{ number_format((float) $order->discount_total, 2) }} {{ $order->currency }}</dd></div>
                @endif
                <div class="total"><dt>{{ __('lms.checkout.total') }}</dt><dd>{{ number_format((float) $order->total, 2) }} <small>{{ $order->currency }}</small></dd></div>
            </dl>
            <div class="co-trust-list">
                <p><span>✓</span>{{ __('lms.checkout.encrypted') }}</p>
                <p><span>✓</span>{{ __('lms.checkout.instant_access') }}</p>
                <p><span>✓</span>{{ __('lms.checkout.secure_provider') }}</p>
            </div>
            <div class="co-powered"><span>{{ __('lms.checkout.powered_by') }}</span><strong>Paymob</strong></div>
        </aside>
    </section>
</main>
