<x-layouts.customer title="Kumachi | Checkout">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/customer/checkout.css') }}">
    @endpush

    <section class="checkout-page">
        <div class="checkout-shell">
            <h1>Checkout</h1>

            @if (session('error') || $errors->any())
                <div class="checkout-feedback" role="alert" aria-live="polite">
                    @if (session('error'))
                        <p class="checkout-feedback-title">We could not place your order.</p>
                        <p class="checkout-feedback-text">{{ session('error') }}</p>
                    @endif

                    @if ($errors->any())
                        <p class="checkout-feedback-title">Please fix the following:</p>
                        <ul class="checkout-feedback-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @php
                $selectedPromoCode = old('promo_code', request()->query('promo_code'));
                $defaultSavedPaymentMethodId = $paymentMethods->firstWhere('is_default', true)?->id ?? $paymentMethods->first()?->id;
                $selectedSavedPaymentMethodId = old('saved_payment_method_id', $defaultSavedPaymentMethodId);
                $promoLookup = $availablePromotions->mapWithKeys(function ($promotion) {
                    return [strtolower(trim($promotion->code)) => (float) $promotion->discount_value];
                });
            @endphp

            <form
                method="POST"
                action="{{ route('checkout.store') }}"
                class="checkout-grid"
                data-promo-lookup='@json($promoLookup)'
                data-available-points="{{ $availablePoints }}"
                data-base-subtotal="{{ $summary['subtotal'] }}"
            >
                @csrf
                <div class="checkout-main">
                    <section class="checkout-panel">
                        <h2>Order Type</h2>
                        <div class="checkout-options">
                            <label class="checkout-choice">
                                <input type="radio" name="order_type" value="pickup" {{ old('order_type', 'pickup') === 'pickup' ? 'checked' : '' }}>
                                <span>Pickup</span>
                            </label>
                            <label class="checkout-choice">
                                <input type="radio" name="order_type" value="delivery" {{ old('order_type') === 'delivery' ? 'checked' : '' }}>
                                <span>Delivery</span>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('order_type')" class="products-form-error" />
                    </section>

                    <section class="checkout-panel" id="deliveryPanel">
                        <h2>Delivery Address</h2>

                        @if ($addresses->isNotEmpty())
                            <div class="checkout-address-list">
                                <label class="checkout-address-item checkout-new-address">
                                    <input type="radio" name="address_id" id="createAddress" value="" {{ old('address_id') === null || old('address_id') === '' ? 'checked' : '' }}>
                                    <span>Add new address</span>
                                </label>

                                @foreach ($addresses as $address)
                                    <label class="checkout-address-item">
                                        <input
                                            type="radio"
                                            name="address_id"
                                            value="{{ $address->id }}"
                                            {{ (string) old('address_id', $address->is_default ? $address->id : null) === (string) $address->id ? 'checked' : '' }}
                                        >
                                        <span>{{ $address->full_name }} • {{ $address->phone }} • {{ $address->street }}, {{ $address->city }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div style="display: none;">
                                <input type="radio" name="address_id" id="createAddress" value="" checked>
                            </div>
                            <p class="checkout-promo-hint">No saved addresses yet. Enter a new address to continue with delivery.</p>
                        @endif

                        <x-input-error :messages="$errors->get('address_id')" class="products-form-error" />

                        <div class="checkout-address-form" id="newAddressFields">
                            <input type="text" name="full_name" placeholder="Full Name" value="{{ old('full_name') }}">
                            <x-input-error :messages="$errors->get('full_name')" class="products-form-error" />
                            <input type="text" name="phone" placeholder="Phone" value="{{ old('phone') }}">
                            <x-input-error :messages="$errors->get('phone')" class="products-form-error" />
                            <input type="text" name="street" placeholder="Street" value="{{ old('street') }}">
                            <x-input-error :messages="$errors->get('street')" class="products-form-error" />
                            <input type="text" name="city" placeholder="City" value="{{ old('city') }}">
                            <x-input-error :messages="$errors->get('city')" class="products-form-error" />
                        </div>
                    </section>

                    <section class="checkout-panel">
                        <h2>Payment Method (Demo)</h2>
                        <div class="checkout-options checkout-payment-options">
                            <label class="checkout-choice">
                                <input type="radio" name="payment_method" value="card" {{ old('payment_method', 'card') === 'card' ? 'checked' : '' }}>
                                <span>Card</span>
                            </label>
                            <label class="checkout-choice checkout-cash-option" data-payment-option="cash">
                                <input type="radio" name="payment_method" value="cash" {{ old('payment_method') === 'cash' ? 'checked' : '' }}>
                                <span>Cash on Pickup</span>
                            </label>
                        </div>
                        @if ($cashPickupRestricted)
                            <p class="checkout-promo-hint" style="color: #b9382a;">
                                Cash on pickup is restricted for your account after {{ $cashPickupFailedCount }} failed pickups. Please use card payment.
                            </p>
                        @elseif ($cashPickupFailedCount >= $cashPickupWarningThreshold)
                            <p class="checkout-promo-hint" style="color: #8a4d1f;">
                                Warning: one more failed pickup will disable cash on pickup. Current failed pickups: {{ $cashPickupFailedCount }}/{{ $cashPickupRestrictionThreshold }}.
                            </p>
                        @endif
                        <x-input-error :messages="$errors->get('payment_method')" class="products-form-error" />

                        @if ($paymentMethods->isNotEmpty())
                            <div class="checkout-saved-cards">
                                <p class="checkout-promo-hint">Choose a saved card or use a new one.</p>

                                <div class="checkout-saved-card-list">
                                    <label class="checkout-choice checkout-saved-card-choice">
                                        <input type="radio" name="saved_payment_method_id" value="" {{ (string) $selectedSavedPaymentMethodId === '' ? 'checked' : '' }}>
                                        <span>Use a new card</span>
                                    </label>

                                    @foreach ($paymentMethods as $paymentMethod)
                                        <label class="checkout-choice checkout-saved-card-choice">
                                            <input
                                                type="radio"
                                                name="saved_payment_method_id"
                                                value="{{ $paymentMethod->id }}"
                                                {{ (string) $selectedSavedPaymentMethodId === (string) $paymentMethod->id ? 'checked' : '' }}
                                            >
                                            <span>{{ $paymentMethod->label }} • {{ $paymentMethod->card_brand ?? 'Card' }} ending {{ $paymentMethod->card_last4 }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <x-input-error :messages="$errors->get('saved_payment_method_id')" class="products-form-error" />

                                <div class="checkout-saved-cvv-panel" id="saved-cvv-panel" style="display: none; margin-top: 0.8rem; padding-top: 0.8rem; border-top: 1px solid #e4c8aa;">
                                    <label class="checkout-mock-field" style="max-width: 150px;">
                                        <span style="font-size: 0.86rem; font-weight: 600; color: #584437; margin-bottom: 0.35rem; display: block;">CVV to confirm</span>
                                        <input type="text" name="saved_card_cvv" placeholder="123" value="{{ old('saved_card_cvv') }}" style="width: 100%; min-height: 2.55rem; border: 1px solid #d8c5b2; border-radius: 0.55rem; padding: 0.5rem 0.75rem; font-size: 0.95rem; color: #3f2a1f; background: #fffcf8;">
                                    </label>
                                    <x-input-error :messages="$errors->get('saved_card_cvv')" class="products-form-error" />
                                </div>
                            </div>
                        @endif

                        <div class="checkout-payment-mocks">
                            <div class="checkout-payment-mock is-visible" data-payment-mock="card">
                                <div class="checkout-card-fields" data-card-fields>
                                    <label class="checkout-mock-field">
                                        <span>Cardholder Name</span>
                                        <input type="text" name="card_holder_name" placeholder="Juan Dela Cruz" value="{{ old('card_holder_name') }}">
                                    </label>
                                    <label class="checkout-mock-field">
                                        <span>Card Number</span>
                                        <input type="text" name="card_number" placeholder="4111 1111 1111 1111" value="{{ old('card_number') }}">
                                    </label>
                                    <div class="checkout-mock-row">
                                        <label class="checkout-mock-field">
                                            <span>Expiry</span>
                                            <input type="text" name="card_expiry" placeholder="12/28" value="{{ old('card_expiry') }}">
                                        </label>
                                        <label class="checkout-mock-field">
                                            <span>CVV</span>
                                            <input type="text" name="card_cvv" placeholder="123" value="{{ old('card_cvv') }}">
                                        </label>
                                    </div>
                                    <div style="margin-top: 0.5rem;">
                                        <label class="checkout-choice" style="display: inline-flex; width: auto; padding: 0.5rem 0.8rem; min-height: unset;">
                                            <input type="checkbox" name="save_new_card" value="1" {{ old('save_new_card') ? 'checked' : '' }}>
                                            <span style="font-size: 0.85rem; font-weight: 500;">Save this card for future use</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="checkout-panel">
                        <h2>Promo & Rewards</h2>


                        <label class="checkout-promo-field">
                            <span class="checkout-promo-label">Promo code</span>
                            <input
                                type="text"
                                name="promo_code"
                                list="available-promo-codes"
                                placeholder="Select a promo code or type an exclusive one"
                                value="{{ $selectedPromoCode }}"
                                class="checkout-promo-input"
                            >
                        </label>
                        <x-input-error :messages="$errors->get('promo_code')" class="products-form-error" />
                        <p class="checkout-promo-helper" data-promo-helper></p>

                        @if ($availablePromotions->isNotEmpty())
                            <datalist id="available-promo-codes">
                                @foreach ($availablePromotions as $promotion)
                                    <option value="{{ $promotion->code }}">{{ $promotion->code }}</option>
                                @endforeach
                            </datalist>

                            <div class="checkout-promo-pills" aria-label="Available promo codes">
                                @foreach ($availablePromotions as $promotion)
                                    <button type="button" class="checkout-promo-pill" data-promo-code="{{ $promotion->code }}">
                                        {{ $promotion->code }}
                                    </button>
                                @endforeach
                            </div>


                        @endif

                        @php
                            $redeemablePoints = (int) (floor($availablePoints / 100) * 100);
                            $redeemToggleDefault = old('redeem_points', 0) > 0;
                        @endphp

                        <div class="checkout-redeem-row">
                            <div class="checkout-redeem-copy">
                                <span class="checkout-promo-label">Redeem points</span>
                                <p class="checkout-promo-hint">
                                    Available: {{ number_format($availablePoints) }} • Redeemable now: {{ number_format($redeemablePoints) }}
                                    <span class="checkout-redeem-rule">(100 points = ₱100 discount)</span>
                                </p>
                            </div>

                            <label class="checkout-toggle {{ $redeemablePoints < 100 ? 'is-disabled' : '' }}">
                                <input
                                    type="checkbox"
                                    name="redeem_points_toggle"
                                    value="1"
                                    {{ $redeemToggleDefault ? 'checked' : '' }}
                                    {{ $redeemablePoints < 100 ? 'disabled' : '' }}
                                    data-redeem-toggle
                                >
                                <span class="checkout-toggle-ui" aria-hidden="true"></span>
                                <span class="checkout-toggle-label">{{ $redeemablePoints < 100 ? 'Need 100+' : 'Apply' }}</span>
                            </label>
                        </div>

                        <input
                            type="hidden"
                            name="redeem_points"
                            value="{{ $redeemToggleDefault ? $redeemablePoints : 0 }}"
                            data-redeem-points-hidden
                            data-redeemable-points="{{ $redeemablePoints }}"
                        >
                        <x-input-error :messages="$errors->get('redeem_points')" class="products-form-error" />
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
                            <strong data-summary-field="subtotal">₱{{ number_format($summary['subtotal'], 2) }}</strong>
                        </div>
                        <div class="summary-row">
                            <span>Promo Discount</span>
                            <strong data-summary-field="promo-discount">- ₱{{ number_format($summary['promotion_discount'] ?? 0, 2) }}</strong>
                        </div>
                        <div class="summary-row">
                            <span>Points Discount</span>
                            <strong data-summary-field="points-discount">- ₱{{ number_format($summary['points_discount'] ?? 0, 2) }}</strong>
                        </div>
                        <div class="summary-row" id="summary-delivery-fee" style="display: none;">
                            <span>Delivery Fee</span>
                            <strong data-summary-field="delivery-fee">₱50.00</strong>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <strong data-summary-field="total">₱{{ number_format($summary['total'], 2) }}</strong>
                        </div>
                        <button type="submit" class="checkout-submit">Proceed to Payment</button>
                    </section>
                </aside>
            </form>
        </div>
    </section>

    <script>
        (() => {
            const deliveryPanel = document.getElementById('deliveryPanel');
            const createAddress = document.getElementById('createAddress');
            const addressInputs = Array.from(document.querySelectorAll('input[name="address_id"]'));
            const newAddressFields = document.getElementById('newAddressFields');
            const orderTypeInputs = Array.from(document.querySelectorAll('input[name="order_type"]'));
            const paymentInputs = Array.from(document.querySelectorAll('input[name="payment_method"]'));
            const cashOption = document.querySelector('[data-payment-option="cash"]');
            const paymentMocks = Array.from(document.querySelectorAll('[data-payment-mock]'));
            const promoInput = document.querySelector('input[name="promo_code"]');
            const promoPills = Array.from(document.querySelectorAll('[data-promo-code]'));
            const redeemPointsHiddenInput = document.querySelector('[data-redeem-points-hidden]');
            const redeemToggle = document.querySelector('[data-redeem-toggle]');
            const savedCardsPanel = document.querySelector('.checkout-saved-cards');
            const savedCardInputs = Array.from(document.querySelectorAll('input[name="saved_payment_method_id"]'));
            const cardFieldsPanel = document.querySelector('[data-payment-mock="card"]');
            const cardFieldsContainer = document.querySelector('[data-card-fields]');
            const cardFieldInputs = Array.from(document.querySelectorAll('[data-card-fields] input:not([name="save_new_card"])'));
            const saveNewCardCheckbox = document.querySelector('[data-card-fields] input[name="save_new_card"]');
            const savedCvvPanel = document.getElementById('saved-cvv-panel');
            const promoHelper = document.querySelector('[data-promo-helper]');
            const summarySubtotal = document.querySelector('[data-summary-field="subtotal"]');
            const summaryPromoDiscount = document.querySelector('[data-summary-field="promo-discount"]');
            const summaryPointsDiscount = document.querySelector('[data-summary-field="points-discount"]');
            const summaryDeliveryFeeRow = document.getElementById('summary-delivery-fee');
            const summaryDeliveryFee = document.querySelector('[data-summary-field="delivery-fee"]');
            const summaryTotal = document.querySelector('[data-summary-field="total"]');
            const checkoutForm = document.querySelector('.checkout-grid');
            const promoLookup = checkoutForm ? JSON.parse(checkoutForm.dataset.promoLookup || '{}') : {};
            const availablePoints = Number(checkoutForm?.dataset.availablePoints || 0);
            const baseSubtotal = Number(checkoutForm?.dataset.baseSubtotal || 0);
            const cashPickupRestricted = {{ $cashPickupRestricted ? 'true' : 'false' }};

            const currency = new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            const normalizePromoCode = (value) => value.trim().toLowerCase();

            const readPromoDiscount = () => {
                if (!promoInput) {
                    return 0;
                }

                return Number(promoLookup[normalizePromoCode(promoInput.value)] ?? 0);
            };

            const readRedeemPoints = () => {
                if (!redeemPointsHiddenInput) {
                    return 0;
                }

                const rawValue = Number.parseInt(redeemPointsHiddenInput.value || '0', 10);
                if (Number.isNaN(rawValue) || rawValue < 0) {
                    return 0;
                }

                return Math.min(rawValue, availablePoints);
            };

            const updateSummary = () => {
                if (!summarySubtotal || !summaryPromoDiscount || !summaryPointsDiscount || !summaryTotal) {
                    return;
                }

                const orderType = orderTypeInputs.find((input) => input.checked)?.value ?? 'pickup';
                const isDelivery = orderType === 'delivery';
                const deliveryFee = isDelivery ? 50.0 : 0.0;

                if (summaryDeliveryFeeRow) {
                    summaryDeliveryFeeRow.style.display = isDelivery ? 'flex' : 'none';
                }

                const promoDiscount = Math.min(readPromoDiscount(), baseSubtotal);
                const pointsDiscount = Math.min(readRedeemPoints() * 1, Math.max(0, baseSubtotal - promoDiscount));
                const total = Math.max(0, baseSubtotal - promoDiscount - pointsDiscount) + deliveryFee;

                summarySubtotal.textContent = currency.format(baseSubtotal);
                summaryPromoDiscount.textContent = `- ${currency.format(promoDiscount)}`;
                summaryPointsDiscount.textContent = `- ${currency.format(pointsDiscount)}`;
                if (summaryDeliveryFee) summaryDeliveryFee.textContent = currency.format(deliveryFee);
                summaryTotal.textContent = currency.format(total);
            };

            const updatePromoHelper = () => {
                if (!promoHelper || !promoInput) {
                    return;
                }

                const currentValue = normalizePromoCode(promoInput.value);

                if (!currentValue) {
                    promoHelper.textContent = '';
                    promoHelper.classList.remove('is-invalid');
                    return;
                }

                if (promoLookup[currentValue]) {
                    promoHelper.textContent = 'Promo code recognized. Discount updates automatically.';
                    promoHelper.classList.remove('is-invalid');
                    return;
                }

                promoHelper.textContent = 'No match in the available promos. If this is an exclusive code, submit it to verify.';
                promoHelper.classList.add('is-invalid');
            };

            const updateCardFields = () => {
                if (!cardFieldsPanel) {
                    return;
                }

                const selectedPayment = paymentInputs.find((input) => input.checked)?.value ?? 'card';
                const isCardPayment = selectedPayment === 'card';
                const selectedSavedCard = savedCardInputs.find((input) => input.checked && input.value !== '')?.closest('label');
                const hasSavedCardSelected = Boolean(selectedSavedCard);

                cardFieldsPanel.style.display = hasSavedCardSelected ? 'none' : 'grid';
                
                cardFieldInputs.forEach((input) => {
                    input.disabled = !isCardPayment || hasSavedCardSelected;
                    input.required = isCardPayment && !hasSavedCardSelected;
                });
                if (saveNewCardCheckbox) {
                    saveNewCardCheckbox.required = false;
                    saveNewCardCheckbox.disabled = !isCardPayment || hasSavedCardSelected;
                }

                if (savedCvvPanel) {
                    savedCvvPanel.style.display = isCardPayment && hasSavedCardSelected ? 'block' : 'none';
                    const cvvInput = savedCvvPanel.querySelector('input');
                    if (cvvInput) {
                        cvvInput.required = isCardPayment && hasSavedCardSelected;
                        cvvInput.disabled = !isCardPayment || !hasSavedCardSelected;
                    }
                }
            };

            promoPills.forEach((pill) => {
                pill.addEventListener('click', () => {
                    if (!promoInput) {
                        return;
                    }

                    promoInput.value = pill.dataset.promoCode ?? '';
                    promoInput.focus();
                    updateSummary();
                });
            });

            promoInput?.addEventListener('input', updateSummary);
            promoInput?.addEventListener('input', updatePromoHelper);
            const syncRedeemToggle = () => {
                if (!redeemPointsHiddenInput || !redeemToggle) {
                    return;
                }

                const redeemable = Number.parseInt(redeemPointsHiddenInput.dataset.redeemablePoints || '0', 10) || 0;
                redeemPointsHiddenInput.value = redeemToggle.checked ? String(redeemable) : '0';
                updateSummary();
            };

            redeemToggle?.addEventListener('change', syncRedeemToggle);
            savedCardInputs.forEach((input) => input.addEventListener('change', updateCardFields));

            const syncCheckoutState = () => {
                const orderType = orderTypeInputs.find((input) => input.checked)?.value ?? 'pickup';
                const isDelivery = orderType === 'delivery';
                if (deliveryPanel) {
                    deliveryPanel.classList.toggle('is-hidden', !isDelivery);
                }
                if (newAddressFields && addressInputs.length > 0) {
                    const isNewAddress = addressInputs.find((input) => input.checked)?.value === '';
                    newAddressFields.classList.toggle('is-visible', isDelivery && isNewAddress);
                }

                if (cashOption) {
                    cashOption.classList.toggle('is-hidden', isDelivery || cashPickupRestricted);
                    const cashInput = cashOption.querySelector('input[name="payment_method"]');
                    if ((isDelivery || cashPickupRestricted) && cashInput?.checked) {
                        const fallbackPayment = paymentInputs.find((input) => input.value === 'card');
                        if (fallbackPayment) {
                            fallbackPayment.checked = true;
                        }
                    }
                }

                const selectedPayment = paymentInputs.find((input) => input.checked)?.value ?? 'card';
                paymentMocks.forEach((panel) => {
                    if (panel.dataset.paymentMock === 'card' && selectedPayment === 'card') {
                        const hasSavedCard = savedCardInputs.find((input) => input.checked && input.value !== '');
                        panel.style.display = hasSavedCard ? 'none' : 'grid';
                    } else {
                        panel.style.display = panel.dataset.paymentMock === selectedPayment ? 'grid' : 'none';
                    }
                });
                
                if (savedCardsPanel) {
                    savedCardsPanel.style.display = selectedPayment === 'card' ? 'grid' : 'none';
                }
                
                updateCardFields();
                
                updateSummary();
            };

            orderTypeInputs.forEach((input) => input.addEventListener('change', syncCheckoutState));
            paymentInputs.forEach((input) => input.addEventListener('change', syncCheckoutState));
            addressInputs.forEach((input) => input.addEventListener('change', syncCheckoutState));
            syncCheckoutState();
            updateSummary();
            updatePromoHelper();
            updateCardFields();
            syncRedeemToggle();
        })();
    </script>
</x-layouts.customer>
