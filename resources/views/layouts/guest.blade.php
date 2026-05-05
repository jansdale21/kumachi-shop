<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $authPageTitle = 'Welcome to Kumachi';
        $authPageSubtitle = 'Sign in to your account to continue';

        if (request()->routeIs('register')) {
            $authPageTitle = 'Join Kumachi';
            $authPageSubtitle = 'Create an account to start ordering';
        } elseif (request()->routeIs('password.request')) {
            $authPageTitle = 'Forgot Password';
            $authPageSubtitle = 'Enter your email and we will send a reset link';
        } elseif (request()->routeIs('password.reset')) {
            $authPageTitle = 'Reset Password';
            $authPageSubtitle = 'Set a new password for your account';
        }
    @endphp

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kumachi') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
    </head>
    <body class="auth-page antialiased">
        <div class="auth-shell">
            <a class="auth-logo" href="{{ url('/') }}" aria-label="{{ config('app.name', 'Kumachi') }} home">
                <img src="{{ asset('media/kumachi-logo.png') }}" alt="{{ config('app.name', 'Kumachi') }} logo">
            </a>

            <h1 class="auth-title">{{ $authPageTitle }}</h1>
            <p class="auth-subtitle">{{ $authPageSubtitle }}</p>

            <div class="auth-card">
                {{ $slot }}
            </div>
        </div>

        <script>
            document.querySelectorAll('[data-password-toggle]').forEach((toggleButton) => {
                toggleButton.addEventListener('click', () => {
                    const inputId = toggleButton.getAttribute('data-password-toggle');
                    const passwordInput = document.getElementById(inputId);

                    if (!passwordInput) {
                        return;
                    }

                    const showPassword = passwordInput.type === 'password';
                    passwordInput.type = showPassword ? 'text' : 'password';

                    toggleButton.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
                    toggleButton.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
                });
            });
        </script>
    </body>
</html>
