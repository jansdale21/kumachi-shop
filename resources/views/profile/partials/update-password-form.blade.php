<section class="profile-section">
    <header class="profile-section-header">
        <h2>Update Password</h2>
        <p>Ensure your account is using a long, random password to stay secure.</p>
    </header>

    <form class="profile-form" method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="profile-field">
            <label class="profile-label" for="update_password_current_password">Current Password</label>
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="profile-input"
                autocomplete="current-password"
            >
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="profile-error" />
        </div>

        <div class="profile-field">
            <label class="profile-label" for="update_password_password">New Password</label>
            <input
                id="update_password_password"
                name="password"
                type="password"
                class="profile-input"
                autocomplete="new-password"
            >
            <x-input-error :messages="$errors->updatePassword->get('password')" class="profile-error" />
        </div>

        <div class="profile-field">
            <label class="profile-label" for="update_password_password_confirmation">Confirm Password</label>
            <input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="profile-input"
                autocomplete="new-password"
            >
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="profile-error" />
        </div>

        <div class="profile-actions">
            <button class="profile-button" type="submit">Save</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="profile-success"
                >Saved.</p>
            @endif
        </div>
    </form>
</section>
