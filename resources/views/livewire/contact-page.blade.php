<main class="contact-page">
    <section class="contact-hero">
        <div class="contact-hero-pattern"></div>
        <div class="cp-container contact-hero-content">
            <div class="contact-hero-copy">
                <span class="contact-eyebrow">{{ __('contacts.hero.eyebrow') }}</span>
                <h1>{{ __('contacts.hero.title') }}</h1>
                <p>{{ __('contacts.hero.description') }}</p>
                <div class="contact-trust">
                    <span>✦</span>
                    <p><strong>{{ __('contacts.hero.trust_title') }}</strong><small>{{ __('contacts.hero.trust_text') }}</small></p>
                </div>
            </div>
            <div class="contact-orbit" aria-hidden="true"><span>✦</span><b>?</b><i>↗</i></div>
        </div>
    </section>

    <section class="cp-container contact-content">
        <aside class="contact-aside">
            <span class="contact-eyebrow">{{ __('contacts.aside.eyebrow') }}</span>
            <h2>{{ __('contacts.aside.title') }}</h2>
            <p>{{ __('contacts.aside.description') }}</p>
            <div class="contact-details">
                <div><span>✉</span><p><small>{{ __('contacts.aside.email') }}</small><strong>{{ config('mail.from.address') }}</strong></p></div>
                <div><span>◷</span><p><small>{{ __('contacts.aside.hours') }}</small><strong>{{ __('contacts.aside.hours_value') }}</strong></p></div>
            </div>
        </aside>

        <form class="contact-form" wire:submit="submit">
            @if (session('success'))
                <div class="contact-success" role="status"><span>✓</span>{{ session('success') }}</div>
            @endif

            <div class="contact-form-heading"><span>01</span><p>{{ __('contacts.form.heading') }}</p></div>
            <div class="contact-fields">
                <label>{{ __('contacts.form.name') }}<input type="text" wire:model="name" placeholder="{{ __('contacts.form.name_placeholder') }}" autocomplete="name">@error('name')<small>{{ $message }}</small>@enderror</label>
                <label>{{ __('contacts.form.email') }}<input type="email" wire:model="email" placeholder="name@example.com" autocomplete="email">@error('email')<small>{{ $message }}</small>@enderror</label>
                <label>{{ __('contacts.form.phone') }}<input type="tel" wire:model="phone" placeholder="{{ __('contacts.form.phone_placeholder') }}" autocomplete="tel">@error('phone')<small>{{ $message }}</small>@enderror</label>
                <label>{{ __('contacts.form.subject') }}<input type="text" wire:model="subject" placeholder="{{ __('contacts.form.subject_placeholder') }}">@error('subject')<small>{{ $message }}</small>@enderror</label>
                <label class="contact-message">{{ __('contacts.form.message') }}<textarea wire:model="message" rows="5" placeholder="{{ __('contacts.form.message_placeholder') }}"></textarea>@error('message')<small>{{ $message }}</small>@enderror</label>
            </div>
            <button class="contact-submit" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submit">{{ __('contacts.form.submit') }} <b>↗</b></span>
                <span wire:loading wire:target="submit">{{ __('contacts.form.sending') }}</span>
            </button>
        </form>
    </section>
</main>
