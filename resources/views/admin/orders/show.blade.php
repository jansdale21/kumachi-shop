<x-layouts.admin title="Kumachi Admin | Order Details">
    @php
        $statusLabel = strtolower((string) $order->status) === 'ready'
            ? (strtolower((string) $order->order_type) === 'delivery' ? 'Ready for Delivery' : 'Ready for Pickup')
            : ucfirst((string) $order->status);
    @endphp
    <section class="admin-order-show-page">
        @if (session('status'))
            <p class="orders-flash">{{ session('status') }}</p>
        @endif

        <header class="order-show-head">
            <div>
                <h1>Order {{ 'KM' . str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</h1>
                <p>Placed on {{ $order->created_at?->format('Y-m-d H:i') }}</p>
            </div>
            <a class="order-show-back ui-btn ui-btn-primary" href="{{ route('admin.orders.index') }}">Back to Orders</a>
        </header>

        <div class="order-show-grid">
            <section class="order-show-panel">
                <h2>Order Summary</h2>
                <dl class="order-summary-list">
                    <div>
                        <dt>Customer</dt>
                        <dd>{{ $order->user?->name ?? 'Guest' }}</dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd>{{ $order->user?->email ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt>Source</dt>
                        <dd>{{ ucfirst((string) $order->order_source) }}</dd>
                    </div>
                    <div>
                        <dt>Order Type</dt>
                        <dd>{{ ucfirst((string) $order->order_type) }}</dd>
                    </div>
                    <div>
                        <dt>Status</dt>
                        <dd>{{ $statusLabel }}</dd>
                    </div>
                    <div>
                        <dt>Payment Status</dt>
                        <dd>
                            @if (strtolower((string) $order->payment_status) === 'refunded')
                                Refunded to original payment method
                            @else
                                {{ ucfirst((string) $order->payment_status) }}
                            @endif
                        </dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="order-status-form order-status-form-inline">
                    @csrf
                    @method('PUT')
                    <label class="products-field" for="orderStatusSelect">
                        <span class="order-status-label">Update Status</span>
                        <select id="orderStatusSelect" name="status" class="order-status-select {{ 'is-' . strtolower((string) $order->status ?? 'pending') }}">
                            @foreach ($statuses as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" {{ $order->status === $statusValue ? 'selected' : '' }}>
                                    {{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="ui-btn ui-btn-primary">Update Status</button>
                </form>
            </section>

            <section class="order-show-panel">
                <h2>Pricing</h2>
                <dl class="order-summary-list">
                    <div>
                        <dt>Items Total</dt>
                        <dd>₱{{ number_format((float) $order->total_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt>Promotion</dt>
                        <dd>{{ $order->promotion?->code ? $order->promotion->code : 'None' }}</dd>
                    </div>
                    <div>
                        <dt>Points Redeemed</dt>
                        <dd>{{ (int) $order->loyaltyTransactions->where('type', 'redeemed')->sum('points') }}</dd>
                    </div>
                    <div>
                        <dt>Points Earned</dt>
                        <dd>{{ (int) $order->loyaltyTransactions->where('type', 'earned')->sum('points') }}</dd>
                    </div>
                    <div>
                        <dt>Delivery Address</dt>
                        <dd>
                            @if ($order->address)
                                {{ $order->address->street ?? '' }}, {{ $order->address->city ?? '' }}
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        <section class="order-show-panel order-items-panel">
            <h2>Order Items</h2>

            <div class="table-wrap">
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product?->name ?? 'Deleted Product' }}</strong>
                                    @if ($item->addons->isNotEmpty())
                                        <small class="order-item-addon-list">
                                            Add-ons:
                                            {{ $item->addons->pluck('addon_name')->join(', ') }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>₱{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>₱{{ number_format((float) ($item->unit_price * $item->quantity), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="orders-empty">No items found for this order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</x-layouts.admin>
