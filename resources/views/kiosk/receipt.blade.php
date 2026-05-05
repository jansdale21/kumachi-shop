<x-layouts.kiosk title="Kumachi | Kiosk Receipt">
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
            <h1 class="orders-title">Kiosk Receipt</h1>

            @if (session('status'))
                <p class="cart-flash">{{ session('status') }}</p>
            @endif

            <article class="order-card">
                <header class="order-card-head">
                    <div>
                        <p class="order-id">{{ 'KM'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</p>
                        <p class="order-date">{{ $order->created_at?->format('Y-m-d • h:i A') }}</p>
                    </div>
                    <div>
                        <span class="order-status is-{{ strtolower((string) $order->status) }}">{{ $statusLabel }}</span>
                    </div>
                </header>

                <div class="order-divider"></div>

                @foreach ($order->items as $item)
                    <div class="checkout-item">
                        <p>{{ $item->quantity }}x {{ $item->product?->name ?? 'Deleted product' }}</p>
                        <strong>₱{{ number_format((float) ($item->unit_price * $item->quantity), 2) }}</strong>
                    </div>
                    @if ($item->addons->isNotEmpty())
                        <small>Add-ons: {{ $item->addons->pluck('addon_name')->implode(', ') }}</small>
                    @endif
                @endforeach

                <div class="order-divider"></div>
                <div class="summary-row">
                    <span>Total</span>
                    <strong>₱{{ number_format((float) $order->total_amount, 2) }}</strong>
                </div>
                <div class="summary-row">
                    <span>Promotion</span>
                    <strong>{{ $order->promotion?->code ? $order->promotion->code : 'None' }}</strong>
                </div>
                <div class="summary-row">
                    <span>Payment</span>
                    <strong>{{ strtoupper((string) $order->payments->last()?->payment_method) }}</strong>
                </div>

                <div class="order-footer-actions">
                    <button class="order-footer-link" type="button" onclick="window.print()">Print Receipt</button>
                    <a class="order-footer-link is-primary" href="{{ route('kiosk.menu') }}">Start New Order</a>
                </div>
            </article>
        </div>
    </section>
</x-layouts.kiosk>

