<div class="student-profile-hero">
    <div class="student-profile-hero__orb"></div>
    <div class="student-profile-hero__content">
        <x-filament::avatar
            :src="filament()->getUserAvatarUrl($student)"
            :alt="$student->name"
            size="w-24 h-24"
            class="student-profile-hero__avatar"
        />

        <div>
            <span class="student-profile-hero__eyebrow">{{ __('lms.profile.student_eyebrow') }}</span>
            <h2>{{ $student->name }}</h2>
            <p>{{ $student->bio ?: __('lms.profile.student_fallback') }}</p>
            <span class="student-profile-hero__status">
                {{ $student->is_active ? __('lms.profile.active') : __('lms.profile.review') }}
            </span>
        </div>
    </div>
</div>

<style>
    .student-profile-hero { position:relative; overflow:hidden; border-radius:1.5rem; padding:clamp(1.5rem,4vw,3rem); color:#fff; background:linear-gradient(125deg,#172554 0%,#1d4ed8 55%,#06b6d4 100%); box-shadow:0 24px 55px -30px rgba(29,78,216,.8) }
    .student-profile-hero__orb { position:absolute; width:24rem; height:24rem; inset-inline-end:-8rem; top:-12rem; border-radius:9999px; background:rgba(255,255,255,.14) }
    .student-profile-hero__content { position:relative; display:flex; align-items:center; gap:1.5rem }
    .student-profile-hero__avatar { flex:none; border:4px solid rgba(255,255,255,.85); box-shadow:0 12px 30px rgba(15,23,42,.3) }
    .student-profile-hero h2 { margin-top:.35rem; font-size:clamp(1.6rem,3vw,2.35rem); font-weight:800; letter-spacing:-.035em }
    .student-profile-hero p { margin-top:.5rem; max-width:46rem; color:rgba(255,255,255,.78) }
    .student-profile-hero__eyebrow { font-size:.72rem; font-weight:800; letter-spacing:.18em; color:#a5f3fc }
    .student-profile-hero__status { display:inline-flex; margin-top:1rem; border:1px solid rgba(255,255,255,.2); border-radius:9999px; padding:.35rem .75rem; font-size:.78rem; font-weight:600; background:rgba(255,255,255,.11) }
    @media(max-width:640px){.student-profile-hero__content{align-items:flex-start;flex-direction:column}}
</style>
