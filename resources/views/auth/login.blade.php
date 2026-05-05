<x-guest-layout>
    <x-auth-session-status class="auth-status" :status="session('status')" />

    @if (session('warning'))
        <p class="auth-status auth-warning" role="status">{{ session('warning') }}</p>
    @endif

    <form class="auth-form" method="POST" action="{{ route('login') }}">
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
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
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

        <div class="auth-row">
            <label class="auth-check" for="remember_me">
                <input id="remember_me" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <span>{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-link-inline" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <button class="auth-submit" type="submit">
            {{ __('Sign In') }}
        </button>
    </form>

    @if (Route::has('register'))
        <p class="auth-switch">
            {{ __('Don\'t have an account?') }}
            <a href="{{ route('register') }}">{{ __('Sign up now') }}</a>
        </p>
    @endif
</x-guest-layout>
