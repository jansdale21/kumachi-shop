<x-layouts.admin title="Kumachi Admin | Purchase Orders">
    <section class="admin-orders-page">
        <header class="orders-head">
            <div class="products-head">
                <h1>Purchase Orders</h1>
                <a class="products-add-button" href="{{ route('admin.purchase-orders.create') }}">+ Add Purchase Order</a>
            </div>
        </header>

        @if (session('status'))
            <p class="orders-flash">{{ session('status') }}</p>
        @endif

        <div class="orders-filters">
            <div class="filter-buttons">
                <a
                    href="{{ route('admin.purchase-orders.index') }}"
                    class="filter-button {{ $currentStatus === '' || $currentStatus === 'all' ? 'is-active' : '' }}"
                >
                    All
                </a>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <a
                        href="{{ route('admin.purchase-orders.index', ['status' => $statusValue]) }}"
                        class="filter-button {{ $currentStatus === $statusValue ? 'is-active' : '' }}"
                    >
                        {{ $statusLabel }}
                    </a>
                @endforeach
            </div>

            <form class="orders-search" method="GET" action="{{ route('admin.purchase-orders.index') }}">
                @if ($currentStatus !== '' && $currentStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <input
                    name="q"
                    type="search"
                    value="{{ $search }}"
                    placeholder="Search by PO ID or Supplier..."
                    autocomplete="off"
                >
                <button type="submit">Search</button>
                @if ($search !== '')
                    <a class="orders-clear-filter" href="{{ route('admin.purchase-orders.index', ['status' => $currentStatus]) }}">Clear</a>
                @endif
            </form>
        </div>

        <section class="orders-table-panel" aria-label="Purchase orders table">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>PO ID</th>
                        <th>Supplier</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchaseOrders as $purchaseOrder)
                        <tr>
                            <td>
                                <strong class="order-id">{{ 'PO' . str_pad((string) $purchaseOrder->id, 6, '0', STR_PAD_LEFT) }}</strong>
                            </td>
                            <td>
                                <div class="order-customer">
                                    <span class="customer-name">{{ $purchaseOrder->supplier?->supplier_name ?? 'Supplier removed' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="order-item-count">{{ $purchaseOrder->items->count() }}</span>
                            </td>
                            <td>
                                <strong class="order-total">₱{{ number_format((float) $purchaseOrder->total_amount, 2) }}</strong>
                            </td>
                            <td>
                                <small class="order-date">{{ $purchaseOrder->created_at?->format('Y-m-d H:i') }}</small>
                            </td>
                            <td>
                                <span class="status-tag is-{{ strtolower((string) $purchaseOrder->status) }}">
                                    {{ ucfirst((string) $purchaseOrder->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a
                                        class="po-action-link"
                                        href="{{ route('admin.purchase-orders.show', $purchaseOrder) }}"
                                        aria-label="{{ $purchaseOrder->status === 'received' ? 'View purchase order details' : 'View or update purchase order' }}"
                                        title="{{ $purchaseOrder->status === 'received' ? 'View Details' : 'View / Update' }}"
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path d="M12 5c-4.5 3-8 6-8 7s3.5 4 8 7c4.5-3 8-6 8-7s-3.5-4-8-7z"></path>
                                            <circle cx="12" cy="12" r="1.5"></circle>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="orders-empty">
                                <p>No purchase orders found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </section>
</x-layouts.admin>
