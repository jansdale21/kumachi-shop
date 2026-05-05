<x-layouts.customer title="Kumachi | Order Details">
    @php
        $statusLabel = strtolower((string) $order->status) === 'ready'
            ? (strtolower((string) $order->order_type) === 'delivery' ? 'Ready for Delivery' : 'Ready for Pickup')
            : ucfirst((string) $order->status);
    @endphp
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/customer/orders.css') }}">
    @endpush

    <section class="orders-page">
        <div class="orders-shell">
            <h1 class="orders-title">Order {{ 'KM'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</h1>

            <article class="order-card">
                <header class="order-card-head">
                    <div>
                        <p class="order-date">{{ $order->created_at?->format('Y-m-d • h:i A') }}</p>
                    </div>
                    <span class="order-status is-{{ strtolower((string) $order->status) }}">{{ $statusLabel }}</span>
                </header>

                <dl class="order-meta">
                    <div>
                        <dt>Order Type</dt>
                        <dd>{{ ucfirst((string) $order->order_type) }}</dd>
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
                    <div>
                        <dt>Total</dt>
                        <dd class="order-total">₱{{ number_format((float) $order->total_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt>Promo</dt>
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
                </dl>

                <div class="order-divider"></div>

                @foreach ($order->items as $item)
                    <div class="checkout-item">
                        <p>{{ $item->quantity }}x {{ $item->product?->name ?? 'Deleted product' }}</p>
                        @if ($item->addons->isNotEmpty())
                            <small>Add-ons: {{ $item->addons->pluck('addon_name')->implode(', ') }}</small>
                        @endif
                    </div>
                @endforeach

                <div class="order-footer-actions">
                    <a class="order-footer-link" href="{{ route('orders.receipt', $order) }}">View Receipt</a>
                    <form method="POST" action="{{ route('orders.reorder', $order) }}">
                        @csrf
                        <button class="order-footer-link order-reorder-button" type="submit">
                            <svg class="order-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 12a9 9 0 1 0 3-6.7"></path>
                                <path d="M3 4v5h5"></path>
                            </svg>
                            Reorder
                        </button>
                    </form>
                    @if (in_array(strtolower((string) $order->status), ['pending', 'preparing'], true))
                        <form method="POST" action="{{ route('orders.cancel', $order) }}" data-confirm="Cancel this order?">
                            @csrf
                            <button class="order-footer-link is-secondary" type="submit">Cancel Order</button>
                        </form>
                    @endif
                </div>
            </article>
        </div>
    </section>
</x-layouts.customer>
