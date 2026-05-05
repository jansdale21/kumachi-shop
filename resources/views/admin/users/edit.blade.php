<x-layouts.admin title="Kumachi Admin | Edit User">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Edit User</h1>
            <a class="products-add-button" href="{{ route('admin.users.index') }}">Back to Users</a>
        </header>

        <section class="products-form-panel">
            <form class="products-form" method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="products-form-grid">
                    <div class="products-field">
                        <label for="name">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
                        <x-input-error :messages="$errors->get('name')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                        <x-input-error :messages="$errors->get('email')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}">
                        <x-input-error :messages="$errors->get('phone')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="role_id">Role</label>
                        <select id="role_id" name="role_id" required>
                            <option value="">Select role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" {{ (string) old('role_id', $user->role_id) === (string) $role->id ? 'selected' : '' }}>
                                    {{ ucfirst($role->role_name) }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role_id')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="1" {{ (string) old('status', (int) $user->status) === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ (string) old('status', (int) $user->status) === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="products-form-error" />
                    </div>
                </div>

                <div class="products-form-actions">
                    <button class="products-add-button" type="submit">Update User</button>
                </div>
            </form>
        </section>
    </section>
</x-layouts.admin>
