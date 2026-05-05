<x-layouts.kiosk title="Kumachi | {{ $product->name }}">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/customer/product-detail.css') }}">
        <link rel="stylesheet" href="{{ asset('css/kiosk/product-detail.css') }}">
    @endpush

    <section class="content-section product-detail-page">
        <div class="product-detail-grid">
            <div class="product-image-shell">
                @if ($product->image_path)
                    <img src="{{ '/storage/'.$product->image_path }}" alt="{{ $product->name }}" class="product-image">
                @else
                    <div class="product-image-fallback">
                        <svg viewBox="0 0 24 24">
                            <path d="M7 4h7a5 5 0 0 1 0 10h-1v1a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4z"></path>
                            <path d="M14 9h1a2 2 0 1 0 0-4h-1"></path>
                            <path d="M8 2v1"></path>
                            <path d="M11 2v1"></path>
                            <path d="M14 2v1"></path>
                        </svg>
                    </div>
                @endif
            </div>

            <div class="product-form-panel">
                <p class="product-category">{{ $product->category?->name ?? 'Coffee' }}</p>
                <h1>{{ $product->name }}</h1>
                <p class="product-description">Customize the drink and add it to the kiosk cart.</p>
                <p class="product-price">₱{{ number_format((float) $product->base_price, 2) }}</p>

                @if (session('status'))
                    <p class="cart-flash">{{ session('status') }}</p>
                @endif

                @if (strtolower((string) $product->availability) !== 'available')
                    <p class="cart-flash cart-flash-error">This item is currently out of stock.</p>
                @endif

                <form method="POST" action="{{ route('kiosk.cart.store', $product) }}" id="addToCartForm">
                    @csrf

                    @if ($product->sizes->isEmpty())
                        <p class="cart-flash cart-flash-error">No size options are configured yet for this product.</p>
                    @endif

                    <section class="customization-block">
                        <h2>Size</h2>
                        <div class="option-grid">
                            @foreach ($product->sizes as $size)
                                <label class="choice-card">
                                    <input
                                        type="radio"
                                        name="product_size_id"
                                        value="{{ $size->id }}"
                                        data-adjustment="{{ $size->price_adjustment }}"
                                        {{ (string) old('product_size_id', (string) $product->sizes->first()?->id) === (string) $size->id ? 'checked' : '' }}
                                    >
                                    <span>{{ $size->size_name }}</span>
                                    <small>{{ (float) $size->price_adjustment > 0 ? '+₱'.number_format((float) $size->price_adjustment, 2) : '+₱0.00' }}</small>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('product_size_id')" class="products-form-error" />
                    </section>

                    <section class="customization-block">
                        <h2>Sugar Level: <span id="sugarLabel">{{ (int) old('sugar_level', 50) }}%</span></h2>
                        <input id="sugarLevel" name="sugar_level" type="range" min="0" max="100" step="25" value="{{ (int) old('sugar_level', 50) }}">
                        <x-input-error :messages="$errors->get('sugar_level')" class="products-form-error" />
                    </section>

                    <section class="customization-block">
                        <h2>Ice Level: <span id="iceLabel">{{ (int) old('ice_level', 50) }}%</span></h2>
                        <input id="iceLevel" name="ice_level" type="range" min="0" max="100" step="25" value="{{ (int) old('ice_level', 50) }}">
                        <x-input-error :messages="$errors->get('ice_level')" class="products-form-error" />
                    </section>

                    <section class="customization-block">
                        <h2>Add-ons</h2>
                        <div class="option-grid">
                            @foreach ($product->addons as $addon)
                                @php
                                    $inv = $addon->inventory;
                                    $usagePerAddon = (float) ($addon->inventory_usage_qty ?? 1);
                                    $factor = (float) ($inv?->units_per_stock_unit ?? 1);
                                    $availableBase = $inv
                                        ? ((float) $inv->quantity) * $factor
                                        : 0.0;
                                    $isAddonAvailable = $addon->inventory_id === null
                                        || ($usagePerAddon <= 0)
                                        || ($availableBase >= $usagePerAddon);
                                @endphp
                                <label class="choice-card {{ $isAddonAvailable ? '' : 'is-unavailable' }}">
                                    <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" data-addon="{{ $addon->price }}" {{ $isAddonAvailable ? '' : 'disabled' }} {{ collect(old('addon_ids', []))->contains($addon->id) ? 'checked' : '' }}>
                                    <span>{{ $addon->name }}</span>
                                    <small>{{ $isAddonAvailable ? '+₱'.number_format((float) $addon->price, 2) : 'Unavailable' }}</small>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('addon_ids')" class="products-form-error" />
                    </section>

                    <section class="customization-block">
                        <h2>Quantity</h2>
                        <div class="qty-control">
                            <button type="button" class="qty-button" data-step="-1">-</button>
                            <input type="number" id="qtyInput" name="quantity" min="1" max="20" value="{{ (int) old('quantity', 1) }}">
                            <button type="button" class="qty-button" data-step="1">+</button>
                        </div>
                        <x-input-error :messages="$errors->get('quantity')" class="products-form-error" />
                    </section>

                    <div class="product-form-footer">
                        <div class="price-summary">
                            <span>Total</span>
                            <strong id="computedTotal">₱0.00</strong>
                        </div>

                        <button
                            type="submit"
                            class="add-to-cart"
                            {{ strtolower((string) $product->availability) !== 'available' || $product->sizes->isEmpty() ? 'disabled' : '' }}
                        >
                            Add to Kiosk Cart
                        </button>

                        <a href="{{ route('kiosk.menu') }}" class="back-link">Back to Kiosk Menu</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        (() => {
            const basePrice = Number({{ (float) $product->base_price }});
            const totalEl = document.getElementById('computedTotal');
            const qtyInput = document.getElementById('qtyInput');
            const sizeInputs = Array.from(document.querySelectorAll('input[name="product_size_id"]'));
            const addonInputs = Array.from(document.querySelectorAll('input[name="addon_ids[]"]'));
            const sugarLevel = document.getElementById('sugarLevel');
            const iceLevel = document.getElementById('iceLevel');
            const sugarLabel = document.getElementById('sugarLabel');
            const iceLabel = document.getElementById('iceLabel');
            const qtyButtons = Array.from(document.querySelectorAll('.qty-button'));

            const compute = () => {
                const sizeAdj = Number(sizeInputs.find((input) => input.checked)?.dataset.adjustment ?? 0);
                const addonsTotal = addonInputs
                    .filter((input) => input.checked)
                    .reduce((sum, input) => sum + Number(input.dataset.addon ?? 0), 0);
                const qty = Math.max(1, Math.min(20, Number(qtyInput?.value ?? 1)));
                const total = (basePrice + sizeAdj + addonsTotal) * qty;
                if (totalEl) totalEl.textContent = `₱${total.toFixed(2)}`;
            };

            const syncLabels = () => {
                if (sugarLabel && sugarLevel) sugarLabel.textContent = `${sugarLevel.value}%`;
                if (iceLabel && iceLevel) iceLabel.textContent = `${iceLevel.value}%`;
            };

            qtyButtons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const step = Number(btn.dataset.step ?? 0);
                    const current = Number(qtyInput?.value ?? 1);
                    const next = Math.max(1, Math.min(20, current + step));
                    if (qtyInput) qtyInput.value = String(next);
                    compute();
                });
            });

            [...sizeInputs, ...addonInputs].forEach((input) => input.addEventListener('change', compute));
            qtyInput?.addEventListener('input', compute);
            sugarLevel?.addEventListener('input', syncLabels);
            iceLevel?.addEventListener('input', syncLabels);

            syncLabels();
            compute();
        })();
    </script>
</x-layouts.kiosk>

