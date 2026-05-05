<x-guest-layout>
    <p class="auth-helper">
        {{ __('Forgot your password? Enter your email and we will send a one-time password (OTP) to reset it.') }}
    </p>

    <x-auth-session-status class="auth-status" :status="session('status')" />

    <form class="auth-form" method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="auth-field">
            <x-input-label class="auth-label" for="email" :value="__('Email Address')" />
            <div class="auth-input-wrap">
                <span class="auth-input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 6h16v12H4z"></path>
                        <path d="m4 7 8 6 8-6"></path>
                    </svg>
                </span>
                <x-text-input
                    id="email"
                    class="auth-input"
                    type="email"
                    name="email"
                    :value="old('email')"
                    placeholder="Enter your email"
                    required
                    autofocus
                />
                <span class="auth-input-end" aria-hidden="true"></span>
            </div>
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <button class="auth-submit" type="submit">{{ __('Send OTP') }}</button>
    </form>

    <p class="auth-switch">
        <a href="{{ route('password.otp', ['email' => old('email')]) }}">{{ __('Have an OTP already? Reset now') }}</a>
    </p>

    <p class="auth-switch">
        <a href="{{ route('login') }}">{{ __('Back to sign in') }}</a>
    </p>
</x-guest-layout>
