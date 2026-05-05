<x-layouts.admin title="Kumachi Admin | Suppliers">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Suppliers</h1>
            <a class="products-add-button" href="{{ route('admin.suppliers.create') }}">+ Add Supplier</a>
        </header>

        @if (session('status'))
            <p class="products-flash">{{ session('status') }}</p>
        @endif

        <form class="products-search" method="GET" action="{{ route('admin.suppliers.index') }}">
            <label class="sr-only" for="supplierSearch">Search suppliers</label>
            <input
                id="supplierSearch"
                name="q"
                type="search"
                value="{{ $search }}"
                placeholder="Search suppliers..."
                autocomplete="off"
            >

            <button type="submit">Apply</button>

            @if ($search !== '')
                <a class="products-clear-filter" href="{{ route('admin.suppliers.index') }}">Clear</a>
            @endif
        </form>

        <section class="suppliers-grid" aria-label="Supplier list">
            @forelse ($suppliers as $supplier)
                <article class="supplier-card">
                    <div class="supplier-card-head">
                        <div>
                            <h2>{{ $supplier->supplier_name }}</h2>
                            <p>{{ $supplier->contact_person ?: 'No contact person set' }}</p>
                        </div>
                        <div class="row-actions">
                            <a href="{{ route('admin.suppliers.edit', $supplier) }}" aria-label="Edit supplier">
                                <svg viewBox="0 0 24 24">
                                    <path d="M4 20h4l10-10-4-4L4 16z"></path>
                                    <path d="m12 6 4 4"></path>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier?');">
                                @csrf
                                @method('DELETE')
                                <button class="danger" type="submit" aria-label="Delete supplier">
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
                    </div>

                    <p>{{ $supplier->email ?: 'No email set' }}</p>
                    <p>{{ $supplier->phone ?: 'No phone set' }}</p>
                    <p>{{ $supplier->address ?: 'No address set' }}</p>

                    <hr>

                    <p><strong>Supplies:</strong></p>
                    <div class="supplier-tags">
                        @forelse ($supplier->inventoryItems as $inventoryItem)
                            <span class="supplier-tag">{{ $inventoryItem->item_name }}</span>
                        @empty
                            <span class="supplier-tag supplier-tag-empty">No linked inventory items</span>
                        @endforelse
                    </div>

                    <hr>

                    <div class="supplier-footer">
                        <span class="status-tag {{ $supplier->is_active ? 'is-available' : 'is-unavailable' }}">
                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <a href="{{ route('admin.suppliers.show', $supplier) }}" class="products-clear-filter">View Details</a>
                    </div>
                </article>
            @empty
                <article class="supplier-card">
                    <h2>No suppliers found.</h2>
                    <p>Add your first supplier.</p>
                </article>
            @endforelse
        </section>
    </section>
</x-layouts.admin>
