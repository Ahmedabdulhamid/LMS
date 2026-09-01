<div class="instructor-profile-hero">
    <div class="instructor-profile-hero__glow"></div>

    <div class="instructor-profile-hero__content">
        <x-filament::avatar
            :src="filament()->getUserAvatarUrl($instructor)"
            :alt="$instructor->name"
            size="w-24 h-24"
            class="instructor-profile-hero__avatar"
        />

        <div class="instructor-profile-hero__identity">
            <span class="instructor-profile-hero__eyebrow">{{ __('lms.profile.instructor_eyebrow') }}</span>
            <h2>{{ $instructor->name }}</h2>
            <p>{{ $instructor->small_description ?: __('lms.profile.instructor_fallback') }}</p>

            <div class="instructor-profile-hero__badges">
                <span>{{ $instructor->is_active ? __('lms.profile.active') : __('lms.profile.review') }}</span>

                @if ($instructor->years_of_experience)
                    <span>{{ __('lms.profile.years', ['count' => $instructor->years_of_experience]) }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .instructor-profile-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.5rem;
        padding: clamp(1.5rem, 4vw, 3rem);
        color: white;
        background: linear-gradient(125deg, #111827 0%, #78350f 52%, #d97706 100%);
        box-shadow: 0 24px 55px -30px rgba(120, 53, 15, .75);
    }

    .instructor-profile-hero__glow {
        position: absolute;
        width: 22rem;
        height: 22rem;
        right: -7rem;
        top: -10rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, .14);
        filter: blur(4px);
    }

    .instructor-profile-hero__content {
        position: relative;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .instructor-profile-hero__avatar {
        flex: none;
        border: 4px solid rgba(255, 255, 255, .82);
        box-shadow: 0 12px 30px rgba(17, 24, 39, .3);
    }

    .instructor-profile-hero__identity h2 {
        margin-top: .35rem;
        font-size: clamp(1.6rem, 3vw, 2.35rem);
        font-weight: 800;
        letter-spacing: -.035em;
    }

    .instructor-profile-hero__identity p {
        margin-top: .5rem;
        max-width: 46rem;
        color: rgba(255, 255, 255, .78);
    }

    .instructor-profile-hero__eyebrow {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .18em;
        color: #fde68a;
    }

    .instructor-profile-hero__badges {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-top: 1rem;
    }

    .instructor-profile-hero__badges span {
        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 9999px;
        padding: .35rem .75rem;
        font-size: .78rem;
        font-weight: 600;
        background: rgba(255, 255, 255, .11);
        backdrop-filter: blur(8px);
    }

    @media (max-width: 640px) {
        .instructor-profile-hero__content {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
