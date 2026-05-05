<x-layouts.kiosk title="Kumachi | Kiosk Checkout">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/customer/checkout.css') }}">
        <link rel="stylesheet" href="{{ asset('css/customer/orders.css') }}">
    @endpush

    <section class="checkout-page">
        <div class="checkout-shell">
            <h1>Kiosk Checkout</h1>

            <form method="POST" action="{{ route('kiosk.checkout.store') }}" class="checkout-grid">
                @csrf
                <input type="hidden" name="order_type" value="pickup">
                <div class="checkout-main">
                    <section class="checkout-panel">
                        <h2>Payment Method (Demo)</h2>
                        <div class="checkout-options checkout-payment-options">
                            <label class="checkout-choice">
                                <input type="radio" name="payment_method" value="card" {{ old('payment_method', 'card') === 'card' ? 'checked' : '' }}>
                                <span>Card</span>
                            </label>
                            <label class="checkout-choice">
                                <input type="radio" name="payment_method" value="gcash" {{ old('payment_method') === 'gcash' ? 'checked' : '' }}>
                                <span>GCash</span>
                            </label>
                            <label class="checkout-choice">
                                <input type="radio" name="payment_method" value="cash" {{ old('payment_method') === 'cash' ? 'checked' : '' }}>
                                <span>Cash</span>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('payment_method')" class="products-form-error" />
                    </section>

                    <section class="checkout-panel">
                        <h2>Promo Code</h2>
                        <input
                            type="text"
                            name="promo_code"
                            placeholder="Enter promo code"
                            value="{{ old('promo_code') }}"
                            class="checkout-promo-input"
                        >
                        <x-input-error :messages="$errors->get('promo_code')" class="products-form-error" />
                        @if ($availablePromotions->isNotEmpty())
                            <p class="checkout-promo-hint">Available: {{ $availablePromotions->pluck('code')->implode(', ') }}</p>
                        @endif
                        <p class="checkout-promo-hint">Rewards points are not applied in kiosk mode because this flow is staff-operated (no customer account binding).</p>
                    </section>

                    <section class="checkout-panel">
                        <h2>Order Items ({{ $cart->items->count() }})</h2>
                        <div class="checkout-items">
                            @foreach ($cart->items as $item)
                                <div class="checkout-item">
                                    <p>{{ $item->quantity }}x {{ $item->product->name }} ({{ $item->size?->size_name }})</p>
                                    @php
                                        $unit = (float) $item->product->base_price + (float) ($item->size?->price_adjustment ?? 0) + $item->addons->sum(fn ($addon) => (float) $addon->price);
                                    @endphp
                                    <strong>₱{{ number_format($unit * $item->quantity, 2) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <aside class="checkout-summary">
                    <section class="checkout-panel">
                        <h2>Order Summary</h2>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong>₱{{ number_format($summary['subtotal'], 2) }}</strong>
                        </div>
                        <div class="summary-row">
                            <span>Promo Discount</span>
                            <strong>- ₱{{ number_format($summary['promotion_discount'] ?? 0, 2) }}</strong>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <strong>₱{{ number_format($summary['total'], 2) }}</strong>
                        </div>
                        <button type="submit" class="checkout-submit">Place Kiosk Order</button>
                    </section>
                </aside>
            </form>
        </div>
    </section>
</x-layouts.kiosk>

