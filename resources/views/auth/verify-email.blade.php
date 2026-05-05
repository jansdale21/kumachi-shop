<x-guest-layout>
    <p class="auth-helper">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="auth-status">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </p>
    @endif

    <div class="auth-actions">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button type="submit" class="auth-submit auth-submit-compact">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="auth-secondary-btn">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
