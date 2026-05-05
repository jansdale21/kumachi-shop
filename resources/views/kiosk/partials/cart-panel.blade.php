@props([
    'cart',
    'summary',
    'compact' => false,
])

<aside class="pos-cart">
    <div class="pos-cart-head">
        <div>
            <h2>Cart</h2>
            <p>{{ $cart->items->count() }} item{{ $cart->items->count() === 1 ? '' : 's' }}</p>
        </div>

        <form method="POST" action="{{ route('kiosk.cart.clear') }}">
            @csrf
            <button type="submit" class="pos-clear" {{ $cart->items->isEmpty() ? 'disabled' : '' }}>Clear</button>
        </form>
    </div>

    @if (session('status'))
        <p class="pos-flash">{{ session('status') }}</p>
    @endif
    @if (session('error'))
        <p class="pos-flash is-error">{{ session('error') }}</p>
    @endif

    <div class="pos-cart-items">
        @forelse ($cart->items as $item)
            @php
                $unitPrice = (float) $item->product->base_price + (float) ($item->size?->price_adjustment ?? 0) + $item->addons->sum(fn ($addon) => (float) $addon->price);
                $lineTotal = $unitPrice * $item->quantity;
            @endphp

            <div class="pos-item">
                <div class="pos-item-main">
                    <div class="pos-item-meta">
                        <strong>{{ $item->product->name }}</strong>
                        <small>
                            {{ $item->size?->size_name ?? 'Default' }}
                            • Sugar {{ $item->sugar_level }}%
                            • Ice {{ $item->ice_level }}%
                            @if ($item->addons->isNotEmpty())
                                • {{ $item->addons->pluck('name')->implode(', ') }}
                            @endif
                        </small>
                    </div>
                    <div class="pos-item-total">₱{{ number_format($lineTotal, 2) }}</div>
                </div>

                <div class="pos-item-actions">
                    <div class="pos-qty" role="group" aria-label="Quantity controls for {{ $item->product->name }}">
                        <form method="POST" action="{{ route('kiosk.cart.items.update', $item) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="quantity" value="{{ max(1, $item->quantity - 1) }}">
                            <button type="submit" class="pos-qty-btn" aria-label="Decrease quantity" {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                        </form>

                        <span class="pos-qty-value" aria-live="polite">{{ $item->quantity }}</span>

                        <form method="POST" action="{{ route('kiosk.cart.items.update', $item) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="quantity" value="{{ min(20, $item->quantity + 1) }}">
                            <button type="submit" class="pos-qty-btn" aria-label="Increase quantity" {{ $item->quantity >= 20 ? 'disabled' : '' }}>+</button>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('kiosk.cart.items.destroy', $item) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="pos-remove">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="pos-empty">
                <p>No items yet.</p>
                <small>Tap a product on the left to add.</small>
            </div>
        @endforelse
    </div>

    <div class="pos-cart-summary">
        <div class="pos-row">
            <span>Subtotal</span>
            <strong>₱{{ number_format($summary['subtotal'] ?? 0, 2) }}</strong>
        </div>
        <div class="pos-row is-total">
            <span>Total</span>
            <strong>₱{{ number_format($summary['total'] ?? 0, 2) }}</strong>
        </div>

        <a class="pos-checkout {{ $cart->items->isEmpty() ? 'is-disabled' : '' }}" href="{{ $cart->items->isEmpty() ? '#' : route('kiosk.checkout.index') }}" aria-disabled="{{ $cart->items->isEmpty() ? 'true' : 'false' }}">
            Checkout
        </a>
    </div>
</aside>

