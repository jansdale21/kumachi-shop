<x-layouts.admin title="Kumachi Admin | Add-ons">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Add-ons</h1>
            <a class="products-add-button" href="{{ route('admin.addons.create') }}">+ Add Add-on</a>
        </header>

        @if (session('status'))
            <p class="products-flash">{{ session('status') }}</p>
        @endif

        <form class="products-search" method="GET" action="{{ route('admin.addons.index') }}">
            <label class="sr-only" for="addonSearch">Search add-ons</label>
            <input
                id="addonSearch"
                name="q"
                type="search"
                value="{{ $search }}"
                placeholder="Search add-ons..."
                autocomplete="off"
            >

            <button type="submit">Apply</button>

            @if ($search !== '')
                <a class="products-clear-filter" href="{{ route('admin.addons.index') }}">Clear</a>
            @endif
        </form>

        <section class="products-table-panel" aria-label="Add-ons table">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Inventory Item</th>
                        <th>Supplier(s)</th>
                        <th>Usage Qty</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($addons as $addon)
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <span class="product-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M5 7h14"></path>
                                            <path d="M12 5v14"></path>
                                            <path d="M7 12h10"></path>
                                        </svg>
                                    </span>
                                    <span>
                                        <strong>{{ $addon->name }}</strong>
                                    </span>
                                </div>
                            </td>
                            <td>₱{{ number_format((float) $addon->price, 2) }}</td>
                            <td>{{ $addon->inventory?->item_name ?? 'Not linked' }}</td>
                            <td>{{ $addon->inventory?->suppliers?->pluck('supplier_name')->join(', ') ?: 'No supplier linked' }}</td>
                            <td>
                                {{ number_format((float) ($addon->inventory_usage_qty ?? 0), 2) }}
                                @if ($addon->inventory?->unit)
                                    {{ $addon->inventory->unit }}
                                @endif
                            </td>
                            <td>{{ $addon->products_count }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.addons.edit', $addon) }}" aria-label="Edit add-on">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M4 20h4l10-10-4-4L4 16z"></path>
                                            <path d="m12 6 4 4"></path>
                                        </svg>
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.addons.destroy', $addon) }}"
                                        onsubmit="return confirm('Delete this add-on?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit" aria-label="Delete add-on">
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
                            <td colspan="7" class="products-empty">No add-ons found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </section>
</x-layouts.admin>
