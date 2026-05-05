<x-guest-layout>
    <x-auth-session-status class="auth-status" :status="session('status')" />

    <form class="auth-form" method="POST" action="{{ route('password.store') }}">
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
                    :value="old('email', $request->email)"
                    placeholder="Enter your email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <span class="auth-input-end" aria-hidden="true"></span>
            </div>
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <div class="auth-field">
            <x-input-label class="auth-label" for="otp" :value="__('OTP Code')" />
            <div class="auth-input-wrap">
                <span class="auth-input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 12h16"></path>
                        <path d="M8 8h.01"></path>
                        <path d="M12 8h.01"></path>
                        <path d="M16 8h.01"></path>
                        <rect x="4" y="5" width="16" height="14" rx="2"></rect>
                    </svg>
                </span>
                <x-text-input
                    id="otp"
                    class="auth-input"
                    type="text"
                    name="otp"
                    :value="old('otp')"
                    placeholder="Enter 6-digit OTP"
                    required
                    maxlength="6"
                    inputmode="numeric"
                />
                <span class="auth-input-end" aria-hidden="true"></span>
            </div>
            <x-input-error :messages="$errors->get('otp')" class="auth-error" />
        </div>

        <div class="auth-field">
            <x-input-label class="auth-label" for="password" :value="__('Password')" />
            <div class="auth-input-wrap">
                <span class="auth-input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                        <path d="M8 11V8a4 4 0 1 1 8 0v3"></path>
                    </svg>
                </span>
                <x-text-input
                    id="password"
                    class="auth-input"
                    type="password"
                    name="password"
                    placeholder="Create a new password"
                    required
                    autocomplete="new-password"
                />
                <button class="auth-input-end auth-visibility-toggle" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                    <svg viewBox="0 0 24 24">
                        <path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6Z"></path>
                        <circle cx="12" cy="12" r="2.5"></circle>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <div class="auth-field">
            <x-input-label class="auth-label" for="password_confirmation" :value="__('Confirm Password')" />
            <div class="auth-input-wrap">
                <span class="auth-input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                        <path d="M8 11V8a4 4 0 1 1 8 0v3"></path>
                    </svg>
                </span>
                <x-text-input
                    id="password_confirmation"
                    class="auth-input"
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirm your password"
                    required
                    autocomplete="new-password"
                />
                <button class="auth-input-end auth-visibility-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false">
                    <svg viewBox="0 0 24 24">
                        <path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6Z"></path>
                        <circle cx="12" cy="12" r="2.5"></circle>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
        </div>

        <button class="auth-submit" type="submit">{{ __('Reset Password') }}</button>
    </form>

    <p class="auth-switch">
        <a href="{{ route('login') }}">{{ __('Back to sign in') }}</a>
    </p>
</x-guest-layout>
