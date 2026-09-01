<main class="sp-page">
    <section class="sp-hero">
        <div class="cp-container">
            <span class="sp-eyebrow">{{ __('lms.subscription_plans.eyebrow') }}</span>
            <h1>{{ __('lms.subscription_plans.title') }}</h1>
            <p>{{ __('lms.subscription_plans.subtitle') }}</p>
        </div>
    </section>

    <section class="cp-container sp-content">
        @if($notice)
            <div class="sp-notice" role="alert">{{ $notice }}</div>
        @endif

        <div class="sp-grid">
            @forelse($plans as $plan)
                <article class="sp-card" wire:key="subscription-plan-{{ $plan->id }}">
                    <div class="sp-card-top">
                        <span>{{ trans_choice('lms.subscription_plans.course_count', $plan->courses_count, ['count' => $plan->courses_count]) }}</span>
                        <h2>{{ $plan->name }}</h2>
                        <p>{{ $plan->description }}</p>
                    </div>
                    <div class="sp-price"><strong>{{ number_format((float) $plan->price, 2) }}</strong> <span>{{ $plan->currency }} / {{ __('lms.subscription_plans.units.'.$plan->duration_unit->value) }}</span></div>
                    <ul class="sp-courses">
                        @foreach($plan->courses as $course)
                            <li><span aria-hidden="true">✓</span>{{ $course->title }}</li>
                        @endforeach
                    </ul>
                    <button class="sp-button" type="button" wire:click="subscribe({{ $plan->id }})" wire:loading.attr="disabled" wire:target="subscribe({{ $plan->id }})">
                        <span wire:loading.remove wire:target="subscribe({{ $plan->id }})">{{ __('lms.subscription_plans.subscribe') }}</span>
                        <span wire:loading wire:target="subscribe({{ $plan->id }})">{{ __('lms.subscription_plans.processing') }}</span>
                    </button>
                </article>
            @empty
                <p class="sp-empty">{{ __('lms.subscription_plans.empty') }}</p>
            @endforelse
        </div>
    </section>
</main>
