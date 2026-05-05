<section class="profile-section">
    <header class="profile-section-header">
        <h2>Delete Account</h2>
        <p>
            Once your account is deleted, all of its resources and data will be permanently deleted.
            Enter your current password to confirm account deletion.
        </p>
    </header>

    <form
        class="profile-form"
        method="post"
        action="{{ route('profile.destroy') }}"
        onsubmit="return confirm('Are you sure you want to permanently delete your account?');"
    >
        @csrf
        @method('delete')

        <div class="profile-field">
            <label class="profile-label" for="delete_password">Password</label>
            <input
                id="delete_password"
                name="password"
                type="password"
                class="profile-input"
                placeholder="Enter your current password"
            >
            <x-input-error :messages="$errors->userDeletion->get('password')" class="profile-error" />
        </div>

        <div class="profile-actions">
            <button class="profile-button profile-button-danger" type="submit">Delete Account</button>
        </div>
    </form>
</section>
