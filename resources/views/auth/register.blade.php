<x-guest-layout>
    <form class="auth-form" method="POST" action="{{ route('register') }}">
        @csrf

        <div class="auth-field">
            <x-input-label class="auth-label" for="name" :value="__('Full Name')" />
            <div class="auth-input-wrap">
                <span class="auth-input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="3.2"></circle>
                        <path d="M5 20a7 7 0 0 1 14 0"></path>
                    </svg>
                </span>
                <x-text-input
                    id="name"
                    class="auth-input"
                    type="text"
                    name="name"
                    :value="old('name')"
                    placeholder="Enter your full name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <span class="auth-input-end" aria-hidden="true"></span>
            </div>
            <x-input-error :messages="$errors->get('name')" class="auth-error" />
        </div>

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
                    autocomplete="username"
                />
                <span class="auth-input-end" aria-hidden="true"></span>
            </div>
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
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
                    placeholder="Create a password"
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

        <button class="auth-submit" type="submit">{{ __('Create Account') }}</button>
    </form>

    <p class="auth-switch">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}">{{ __('Sign in') }}</a>
    </p>
</x-guest-layout>
