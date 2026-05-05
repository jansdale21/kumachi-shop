<x-layouts.admin title="Kumachi Admin | Orders">
    <section class="admin-orders-page">
        <header class="orders-head">
            <h1>Order Management</h1>
        </header>

        @if (session('status'))
            <p class="orders-flash">{{ session('status') }}</p>
        @endif

        <div class="orders-filters">
            @php
                $baseFilters = array_filter([
                    'q' => $search !== '' ? $search : null,
                    'from' => $from !== '' ? $from : null,
                    'to' => $to !== '' ? $to : null,
                ]);
            @endphp

            <div class="filter-buttons">
                <a
                    href="{{ route('admin.orders.index', $baseFilters) }}"
                    class="filter-button {{ $currentStatus === '' || $currentStatus === 'all' ? 'is-active' : '' }}"
                >
                    All
                </a>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <a
                        href="{{ route('admin.orders.index', array_merge($baseFilters, ['status' => $statusValue])) }}"
                        class="filter-button {{ $currentStatus === $statusValue ? 'is-active' : '' }}"
                    >
                        {{ $statusLabel }}
                    </a>
                @endforeach
            </div>

            <form class="orders-search" method="GET" action="{{ route('admin.orders.index') }}">
                @if ($currentStatus !== '' && $currentStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <label class="sr-only" for="ordersFromDate">From date</label>
                <input
                    id="ordersFromDate"
                    name="from"
                    type="date"
                    value="{{ $from }}"
                >
                <label class="sr-only" for="ordersToDate">To date</label>
                <input
                    id="ordersToDate"
                    name="to"
                    type="date"
                    value="{{ $to }}"
                >
                <input
                    name="q"
                    type="search"
                    value="{{ $search }}"
                    placeholder="Search by Order ID or Customer..."
                    autocomplete="off"
                >
                <button type="submit">Search</button>
                @if ($search !== '' || $from !== '' || $to !== '')
                    <a
                        class="orders-clear-filter"
                        href="{{ route('admin.orders.index', $currentStatus !== '' && $currentStatus !== 'all' ? ['status' => $currentStatus] : []) }}"
                    >
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <section class="orders-table-panel" aria-label="Orders table">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Source</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <strong class="order-id">{{ 'KM' . str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</strong>
                            </td>
                            <td>
                                <div class="order-customer">
                                    @if (strtolower((string) $order->order_source) === 'kiosk')
                                        <span class="customer-name">Kiosk Walk-in Customer</span>
                                        <small class="customer-email">Placed via kiosk</small>
                                    @else
                                        <span class="customer-name">{{ $order->user?->name ?? 'Online Customer' }}</span>
                                        <small class="customer-email">{{ $order->user?->email ?? 'No email available' }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="order-item-count">{{ $order->items->count() }}</span>
                            </td>
                            <td>
                                <strong class="order-total">₱{{ number_format($order->total_amount, 2) }}</strong>
                            </td>
                            <td>
                                <span class="order-source-badge {{ strtolower($order->order_source) === 'online' ? 'is-online' : 'is-kiosk' }}">
                                    {{ ucfirst($order->order_source ?? 'Online') }}
                                </span>
                            </td>
                            <td>
                                <small class="order-date">{{ $order->created_at->format('Y-m-d H:i') }}</small>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="order-status-form">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="order-status-select {{ 'is-' . strtolower($order->status ?? 'pending') }}" onchange="this.form.submit()">
                                        @foreach ($statuses as $statusValue => $statusLabel)
                                            <option value="{{ $statusValue }}" {{ $order->status === $statusValue ? 'selected' : '' }}>
                                                {{ $statusLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.orders.show', $order) }}" aria-label="View order details" title="View Details">
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
                            <td colspan="8" class="orders-empty">
                                <p>No orders found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </section>
</x-layouts.admin>
