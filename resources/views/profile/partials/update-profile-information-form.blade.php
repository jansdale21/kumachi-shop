@php
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->map(fn (string $namePart) => strtoupper(substr($namePart, 0, 1)))
        ->take(2)
        ->join('');
@endphp

<section class="profile-section">

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form class="profile-form profile-overview-form" method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="profile-overview-head">
            <div class="profile-avatar">{{ $initials ?: 'U' }}</div>
            <div class="profile-user-meta">
                <h2>{{ old('name', $user->name) }}</h2>
            </div>
            <button class="profile-button profile-button-small" type="submit">Save</button>
        </div>

        <div class="profile-field-grid">
            <div class="profile-field">
                <label class="profile-label" for="name">Full Name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="profile-input"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    autocomplete="name"
                >
                <x-input-error class="profile-error" :messages="$errors->get('name')" />
            </div>

            <div class="profile-field">
                <label class="profile-label" for="email">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="profile-input"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="username"
                >
                <x-input-error class="profile-error" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="profile-inline-note">
                        <p>Your email address is unverified.</p>
                        <button class="profile-link-button" form="send-verification" type="submit">
                            Click here to re-send the verification email.
                        </button>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <p class="profile-success">A new verification link has been sent to your email address.</p>
                    @endif
                @endif
            </div>

            <div class="profile-field">
                <label class="profile-label" for="phone">Phone</label>
                <input
                    id="phone"
                    name="phone"
                    type="text"
                    class="profile-input"
                    value="{{ old('phone', data_get($user, 'phone')) }}"
                    autocomplete="tel"
                    placeholder="Enter your phone number"
                >
                <x-input-error class="profile-error" :messages="$errors->get('phone')" />
            </div>
        </div>

        @if (session('status') === 'profile-updated')
            <div class="profile-actions">
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="profile-success"
                >Saved.</p>
            </div>
        @endif
    </form>
</section>
