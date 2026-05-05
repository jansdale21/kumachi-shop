<x-layouts.admin title="Kumachi Admin | Users">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Users</h1>
            <a class="products-add-button" href="{{ route('admin.users.create') }}">+ Add User</a>
        </header>

        @if (session('status'))
            <p class="products-flash">{{ session('status') }}</p>
        @endif
        @if (session('error'))
            <p class="products-flash products-flash-error">{{ session('error') }}</p>
        @endif

        <form class="products-search" method="GET" action="{{ route('admin.users.index') }}">
            <label class="sr-only" for="userRole">Filter by role</label>
            <select id="userRole" name="role">
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected($roleId === $role->id)>
                        {{ ucfirst($role->role_name) }}
                    </option>
                @endforeach
            </select>

            <label class="sr-only" for="userSearch">Search users</label>
            <input
                id="userSearch"
                name="q"
                type="search"
                value="{{ $search }}"
                placeholder="Search users..."
                autocomplete="off"
            >

            <button type="submit">Apply</button>

            @if ($roleId || $search !== '')
                <a class="products-clear-filter" href="{{ route('admin.users.index') }}">Clear</a>
            @endif
        </form>

        <section class="products-table-panel" aria-label="Users table">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Loyalty Points</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <span class="product-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <circle cx="12" cy="8" r="3.2"></circle>
                                            <path d="M5 20a7 7 0 0 1 14 0"></path>
                                        </svg>
                                    </span>
                                    <span>
                                        <strong>{{ $user->name }}</strong>
                                        <small>{{ $user->created_at?->format('M d, Y') }}</small>
                                    </span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?: '-' }}</td>
                            <td>{{ ucfirst((string) $user->role?->role_name) }}</td>
                            <td>{{ number_format((int) $user->loyalty_points) }}</td>
                            <td>
                                <span class="status-tag {{ $user->status ? 'is-available' : 'is-unavailable' }}">
                                    {{ $user->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.users.edit', $user) }}" aria-label="Edit user">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M4 20h4l10-10-4-4L4 16z"></path>
                                            <path d="m12 6 4 4"></path>
                                        </svg>
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.destroy', $user) }}"
                                        onsubmit="return confirm('Delete this user?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit" aria-label="Delete user">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M4 7h16"></path>
                                                <path d="M10 11v6"></path>
                                                <path d="M14 11v6"></path>
                                                <path d="M6 7l1 13h10l1-13"></path>
                                                <path d="M9 7V4h6v3"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="products-empty">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </section>
</x-layouts.admin>
