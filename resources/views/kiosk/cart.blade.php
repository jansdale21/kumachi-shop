<x-layouts.kiosk title="Kumachi | Kiosk Cart">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/customer/cart.css') }}">
    @endpush

    <section class="content-section cart-page">
        <div class="cart-shell">
            <h1>Kiosk Cart</h1>

            @if (session('status'))
                <p class="cart-flash">{{ session('status') }}</p>
            @endif
            @if (session('error'))
                <p class="cart-flash cart-flash-error">{{ session('error') }}</p>
            @endif

            @if ($cart->items->isEmpty())
                <section class="cart-panel cart-empty">
                    <p>Your cart is empty.</p>
                    <a href="{{ route('kiosk.menu') }}">Browse Kiosk Menu</a>
                </section>
            @else
                <section class="cart-panel">
                    @foreach ($cart->items as $item)
                        @php
                            $unitPrice = (float) $item->product->base_price + (float) ($item->size?->price_adjustment ?? 0) + $item->addons->sum(fn ($addon) => (float) $addon->price);
                            $lineTotal = $unitPrice * $item->quantity;
                        @endphp
                        <article class="cart-item">
                            <div class="cart-item-main">
                                <div class="cart-item-meta">
                                    <h2>{{ $item->product->name }}</h2>
                                    <p>{{ $item->size?->size_name ?? 'Default size' }}</p>
                                    <p>Sugar: {{ $item->sugar_level }}% • Ice: {{ $item->ice_level }}%</p>
                                    @if ($item->addons->isNotEmpty())
                                        <p>Add-ons: {{ $item->addons->pluck('name')->implode(', ') }}</p>
                                    @endif
                                </div>

                                <div class="cart-item-controls">
                                    <form method="POST" action="{{ route('kiosk.cart.items.update', $item) }}" class="qty-form" data-qty-form>
                                        @csrf
                                        @method('PATCH')
                                        <button type="button" class="qty-step qty-minus" data-qty-step="-1" aria-label="Decrease quantity">−</button>
                                        <input type="number" name="quantity" min="1" max="20" value="{{ $item->quantity }}" data-qty-input>
                                        <button type="button" class="qty-step qty-plus" data-qty-step="1" aria-label="Increase quantity">+</button>
                                    </form>

                                    <form method="POST" action="{{ route('kiosk.cart.items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="remove-button">Remove</button>
                                    </form>
                                </div>
                            </div>

                            <p class="line-total">₱{{ number_format($lineTotal, 2) }}</p>
                        </article>
                    @endforeach
                </section>

                <section class="cart-panel summary-panel">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>₱{{ number_format($summary['subtotal'], 2) }}</strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <strong>₱{{ number_format($summary['total'], 2) }}</strong>
                    </div>
                    <a href="{{ route('kiosk.checkout.index') }}" class="checkout-button">Proceed to Checkout</a>
                    <a href="{{ route('kiosk.menu') }}" class="continue-link">Continue Ordering</a>
                </section>
            @endif
        </div>
    </section>

    <script>
        (() => {
            document.querySelectorAll('[data-qty-form]').forEach((form) => {
                const input = form.querySelector('[data-qty-input]');
                const steps = form.querySelectorAll('[data-qty-step]');

                const submitForm = () => form.requestSubmit();

                steps.forEach((button) => {
                    button.addEventListener('click', () => {
                        const step = Number.parseInt(button.dataset.qtyStep || '0', 10);
                        const current = Number.parseInt(input.value || '1', 10);
                        const next = Math.max(1, Math.min(20, current + step));

                        input.value = String(next);
                        submitForm();
                    });
                });

                input.addEventListener('change', () => {
                    const current = Math.max(1, Math.min(20, Number.parseInt(input.value || '1', 10)));
                    input.value = String(current);
                    submitForm();
                });
            });
        })();
    </script>
</x-layouts.kiosk>

