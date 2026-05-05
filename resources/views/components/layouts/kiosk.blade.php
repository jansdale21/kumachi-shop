@props(['title' => config('app.name', 'Kumachi').' | Kiosk'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>
        <link rel="stylesheet" href="{{ asset('css/layouts/kiosk.css') }}">
        @stack('styles')
    </head>
    <body>
        <div class="kiosk-shell">
            <header class="kiosk-topbar">
                <div class="kiosk-topbar-inner">
                    <a class="kiosk-brand" href="{{ route('kiosk') }}">
                        <span class="kiosk-brand-mark" aria-hidden="true">
                            <img src="{{ asset('media/kumachi-logo.png') }}" alt="{{ config('app.name', 'Kumachi') }} logo">
                        </span>
                        <span class="kiosk-brand-copy">
                            <strong>Kiosk</strong>
                            <small>{{ config('app.name', 'Kumachi') }}</small>
                        </span>
                    </a>

                    <nav class="kiosk-actions" aria-label="Kiosk actions">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="kiosk-action is-danger" type="submit">Logout</button>
                        </form>
                    </nav>
                </div>
            </header>

            <main class="kiosk-content">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>

