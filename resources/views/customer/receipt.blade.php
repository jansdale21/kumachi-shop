<x-layouts.customer title="Kumachi | Receipt">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/customer/orders.css') }}">
    @endpush

    <section class="orders-page">
        <div class="orders-shell">
            <h1 class="orders-title">Receipt</h1>

            <article class="order-card">
                <header class="order-card-head">
                    <div>
                        <p class="order-id">{{ 'KM'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</p>
                        <p class="order-date">{{ $order->created_at?->format('Y-m-d • h:i A') }}</p>
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
                    <span>Total Paid</span>
                    <strong>₱{{ number_format((float) $order->total_amount, 2) }}</strong>
                </div>
                <div class="summary-row">
                    <span>Promotion</span>
                    <strong>{{ $order->promotion?->code ? $order->promotion->code : 'None' }}</strong>
                </div>
                <div class="summary-row">
                    <span>Points Redeemed</span>
                    <strong>{{ (int) $order->loyaltyTransactions->where('type', 'redeemed')->sum('points') }}</strong>
                </div>
                <div class="summary-row">
                    <span>Points Earned</span>
                    <strong>{{ (int) $order->loyaltyTransactions->where('type', 'earned')->sum('points') }}</strong>
                </div>
                <div class="summary-row">
                    <span>Payment Method</span>
                    <strong>{{ strtoupper((string) $order->payments->last()?->payment_method) }}</strong>
                </div>

                <div class="order-footer-actions">
                    <button class="order-footer-link is-primary" type="button" onclick="window.print()">Print Receipt</button>
                    <a class="order-footer-link is-secondary" href="{{ route('orders.show', $order) }}">Back to Order Details</a>
                    <form method="POST" action="{{ route('orders.reorder', $order) }}">
                        @csrf
                        <button class="order-footer-link is-primary order-reorder-button" type="submit">
                            <svg class="order-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 12a9 9 0 1 0 3-6.7"></path>
                                <path d="M3 4v5h5"></path>
                            </svg>
                            Reorder
                        </button>
                    </form>
                </div>
            </article>
        </div>
    </section>
</x-layouts.customer>
