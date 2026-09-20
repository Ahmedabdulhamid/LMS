@php
    $registrationUrl = filament()->getRegistrationUrl();
    $passwordResetUrl = filament()->getRequestPasswordResetUrl();
@endphp
<div class="lms-login-links">
    @if($passwordResetUrl)
        <a class="lms-login-link lms-login-link-secondary" href="{{ $passwordResetUrl }}"><x-filament::icon icon="heroicon-o-key" /><span>{{ __('auth-links.forgot_password') }}</span></a>
    @endif
    @if($registrationUrl)
        <div class="lms-login-divider"><span>{{ __('auth-links.new_here') }}</span></div>
        <a class="lms-login-link lms-login-link-primary" href="{{ $registrationUrl }}"><x-filament::icon icon="heroicon-o-user-plus" /><span>{{ __('auth-links.create_account') }}</span></a>
    @endif
</div>
<style>
.lms-login-links{display:grid;gap:.8rem;margin-top:1.15rem}.lms-login-link{display:flex;align-items:center;justify-content:center;gap:.55rem;width:100%;padding:.78rem 1rem;border-radius:.8rem;font-size:.875rem;font-weight:750;text-decoration:none;transition:transform .15s,background .15s}.lms-login-link:hover{transform:translateY(-1px)}.lms-login-link svg{width:1.15rem;height:1.15rem}.lms-login-link-secondary{border:1px solid rgb(228 228 231);color:rgb(63 63 70);background:rgb(250 250 250)}.lms-login-link-primary{color:rgb(24 24 27);background:rgb(245 158 11);box-shadow:0 8px 20px rgba(245,158,11,.2)}.lms-login-divider{display:flex;align-items:center;gap:.7rem;color:rgb(161 161 170);font-size:.7rem}.lms-login-divider::before,.lms-login-divider::after{height:1px;flex:1;content:'';background:rgb(228 228 231)}.dark .lms-login-link-secondary{border-color:rgb(63 63 70);color:rgb(228 228 231);background:rgb(39 39 42)}.dark .lms-login-divider::before,.dark .lms-login-divider::after{background:rgb(63 63 70)}
</style>
