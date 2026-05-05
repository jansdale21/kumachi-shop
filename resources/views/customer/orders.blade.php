<x-layouts.customer title="Kumachi | Orders">
    @php
        $filters = [
            'all' => 'All',
            'pending' => 'Pending',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];
        $orderStatusLabel = static function ($order): string {
            $status = strtolower((string) $order->status);
            if ($status !== 'ready') {
                return ucfirst((string) $order->status);
            }

            return strtolower((string) $order->order_type) === 'delivery'
                ? 'Ready for Delivery'
                : 'Ready for Pickup';
        };
    @endphp
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/customer/orders.css') }}">
    @endpush

    <section class="orders-page">
        <div class="orders-shell">
            <h1 class="orders-title">Order History</h1>

            <div id="ordersFilters" class="orders-filters" role="tablist" aria-label="Order status filters">
                @foreach ($filters as $value => $label)
                    <button
                        class="orders-filter {{ $value === 'all' ? 'is-active' : '' }}"
                        type="button"
                        data-status="{{ $value }}"
                    >{{ $label }}</button>
                @endforeach
            </div>

            <section class="order-list" id="ordersList" aria-label="Order list">
                @forelse ($orders as $order)
                    @php
                        $status = strtolower((string) $order->status);
                        $statusLabel = $status === 'ready'
                            ? $orderStatusLabel($order)
                            : ($filters[$status] ?? ucfirst((string) $order->status));
                    @endphp
                    <article class="order-card">
                        <header class="order-card-head">
                            <div>
                                <p class="order-id">{{ 'KM'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</p>
                                <p class="order-date">{{ $order->created_at?->format('Y-m-d • h:i A') }}</p>
                            </div>
                            <span class="order-status is-{{ strtolower((string) $order->status) }}">{{ $statusLabel }}</span>
                        </header>

                        <dl class="order-meta" data-order-status="{{ strtolower((string) $order->status) }}">
                            <div>
                                <dt>Items</dt>
                                <dd>{{ $order->items->sum('quantity') }}</dd>
                            </div>
                            <div>
                                <dt>Total</dt>
                                <dd class="order-total">₱{{ number_format((float) $order->total_amount, 2) }}</dd>
                            </div>
                            <div>
                                <dt>Type</dt>
                                <dd>{{ ucfirst((string) $order->order_type) }}</dd>
                            </div>
                            <div>
                                <dt>Payment</dt>
                                <dd>
                                    @if (strtolower((string) $order->payment_status) === 'refunded')
                                        Refunded to original payment method
                                    @else
                                        {{ ucfirst((string) $order->payment_status) }}
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <a class="order-link" href="{{ route('orders.show', $order) }}">
                                    <svg class="order-icon" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z"></path>
                                        <circle cx="12" cy="12" r="2.5"></circle>
                                    </svg>
                                    View Details
                                </a>
                            </div>
                        </dl>

                        <div class="order-divider"></div>
                        <div class="order-footer-actions">
                            <a class="order-footer-link" href="{{ route('orders.receipt', $order) }}">
                                <svg class="order-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M7 3h7l5 5v13H7z"></path>
                                    <path d="M14 3v5h5"></path>
                                </svg>
                                View Receipt
                            </a>
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
                @empty
                    <article class="order-card">
                        <p class="order-date">No orders yet. Place your first order from the menu.</p>
                    </article>
                @endforelse
            </section>

            <p id="ordersEmpty" class="orders-empty">No orders found for the selected filter.</p>
        </div>
    </section>

    <script>
        (() => {
            const filterButtons = Array.from(document.querySelectorAll('#ordersFilters .orders-filter'));
            const cards = Array.from(document.querySelectorAll('#ordersList .order-card'));
            const emptyState = document.getElementById('ordersEmpty');

            if (filterButtons.length === 0 || cards.length === 0 || !emptyState) {
                return;
            }

            let activeStatus = 'all';

            const runFilter = () => {
                let visibleCount = 0;

                cards.forEach((card) => {
                    const statusEl = card.querySelector('[data-order-status]');
                    const status = statusEl ? statusEl.dataset.orderStatus : '';
                    const isVisible = activeStatus === 'all' || status === activeStatus;

                    card.classList.toggle('is-hidden', !isVisible);

                    if (isVisible) {
                        visibleCount++;
                    }
                });

                emptyState.classList.toggle('is-visible', visibleCount === 0);
            };

            filterButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    activeStatus = button.dataset.status || 'all';

                    filterButtons.forEach((chip) => {
                        chip.classList.toggle('is-active', chip === button);
                    });

                    runFilter();
                });
            });
        })();
    </script>
</x-layouts.customer>
