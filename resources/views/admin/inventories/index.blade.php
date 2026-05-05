<x-layouts.admin title="Kumachi Admin | Inventory">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Inventory</h1>
            <a class="products-add-button" href="{{ route('admin.inventories.create') }}">+ Add Inventory Item</a>
        </header>

        @if (session('status'))
            <p class="products-flash">{{ session('status') }}</p>
        @endif

        <form class="products-search" method="GET" action="{{ route('admin.inventories.index') }}">
            <label class="sr-only" for="inventorySearch">Search inventory</label>
            <input
                id="inventorySearch"
                name="q"
                type="search"
                value="{{ $search }}"
                placeholder="Search inventory..."
                autocomplete="off"
            >
            <button type="submit">Apply</button>
            @if ($search !== '')
                <a class="products-clear-filter" href="{{ route('admin.inventories.index') }}">Clear</a>
            @endif
        </form>

        <section class="products-table-panel" aria-label="Inventory table">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Unit</th>
                        <th>Quantity</th>
                        <th>Reorder Level</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventories as $inventory)
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <span class="product-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M6 20h12"></path>
                                            <path d="M7 20V7l5-3 5 3v13"></path>
                                            <path d="M9.5 10h5"></path>
                                        </svg>
                                    </span>
                                    <span>
                                        <strong>{{ $inventory->item_name }}</strong>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <strong>{{ strtoupper((string) $inventory->unit) }}</strong>
                                @if (($inventory->base_unit ?? $inventory->unit) !== $inventory->unit)
                                    <div><small>Usage: {{ strtoupper((string) ($inventory->base_unit ?? $inventory->unit)) }}</small></div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $factor = (float) ($inventory->units_per_stock_unit ?? 1);
                                    $baseUnit = (string) ($inventory->base_unit ?? $inventory->unit);
                                    $showConversion = abs($factor - 1.0) > 0.0001 || $baseUnit !== (string) $inventory->unit;
                                @endphp
                                {{ number_format((float) $inventory->quantity, 2) }} {{ $inventory->unit }}
                                @if ($showConversion)
                                    <div>
                                        <small>
                                            ≈ {{ number_format((float) ($inventory->quantity * $factor), 2) }}
                                            {{ $baseUnit }}
                                        </small>
                                    </div>
                                @endif
                            </td>
                            <td>
                                {{ number_format((float) $inventory->reorder_level, 2) }} {{ $inventory->unit }}
                                @if ($showConversion)
                                    <div>
                                        <small>
                                            ≈ {{ number_format((float) ($inventory->reorder_level * $factor), 2) }}
                                            {{ $baseUnit }}
                                        </small>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $factor = (float) ($inventory->units_per_stock_unit ?? 1);
                                    $onHandBase = (float) $inventory->quantity * $factor;
                                    $reorderBase = (float) $inventory->reorder_level * $factor;
                                    $isOutOfStock = $onHandBase <= 0;
                                    $isLowStock = ! $isOutOfStock && $onHandBase <= $reorderBase;
                                @endphp
                                <span @class([
                                    'status-tag',
                                    'is-out-of-stock' => $isOutOfStock,
                                    'is-unavailable' => $isLowStock,
                                    'is-available' => ! $isOutOfStock && ! $isLowStock,
                                ])>
                                    @if ($isOutOfStock)
                                        No Stock
                                    @elseif ($isLowStock)
                                        Low Stock
                                    @else
                                        In Stock
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.inventories.show', $inventory) }}" aria-label="View inventory item">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                            <circle cx="12" cy="12" r="2.5"></circle>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.inventories.edit', $inventory) }}" aria-label="Edit inventory item">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M4 20h4l10-10-4-4L4 16z"></path>
                                            <path d="m12 6 4 4"></path>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.inventories.destroy', $inventory) }}" onsubmit="return confirm('Delete this inventory item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit" aria-label="Delete inventory item">
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
                            <td colspan="6" class="products-empty">No inventory items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </section>
</x-layouts.admin>
