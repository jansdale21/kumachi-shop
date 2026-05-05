<x-layouts.customer title="Kumachi | {{ $product->name }}">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/customer/product-detail.css') }}">
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
                <p class="product-description">Rich and handcrafted coffee made fresh to your preference.</p>
                <p class="product-price">₱{{ number_format((float) $product->base_price, 2) }}</p>

                @if (session('status'))
                    <p class="cart-flash">{{ session('status') }}</p>
                @endif

                @if (strtolower((string) $product->availability) !== 'available')
                    <p class="cart-flash cart-flash-error">This item is currently out of stock.</p>
                @endif

                @auth
                    <form method="POST" action="{{ route('cart.store', $product) }}" id="addToCartForm" data-base-price="{{ (float) $product->base_price }}">
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
                                    <label class="choice-card addon-choice {{ $isAddonAvailable ? '' : 'is-unavailable' }}">
                                        <input
                                            type="checkbox"
                                            name="addon_ids[]"
                                            value="{{ $addon->id }}"
                                            data-price="{{ $addon->price }}"
                                            {{ $isAddonAvailable ? '' : 'disabled' }}
                                            {{ in_array((string) $addon->id, array_map('strval', old('addon_ids', [])), true) ? 'checked' : '' }}
                                        >
                                        <span>{{ $addon->name }}</span>
                                        <small>
                                            @if ($isAddonAvailable)
                                                +₱{{ number_format((float) $addon->price, 2) }}
                                            @else
                                                Unavailable
                                            @endif
                                        </small>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('addon_ids')" class="products-form-error" />
                        </section>

                        <section class="customization-block qty-block">
                            <h2>Quantity</h2>
                            <div class="qty-control">
                                <button type="button" id="qtyMinus">−</button>
                                <input id="quantity" name="quantity" type="number" min="1" max="20" value="{{ old('quantity', 1) }}">
                                <button type="button" id="qtyPlus">+</button>
                            </div>
                            <x-input-error :messages="$errors->get('quantity')" class="products-form-error" />
                        </section>

                        <div class="total-line">
                            <span>Total:</span>
                            <strong id="totalPrice">₱0.00</strong>
                        </div>

                        <button type="submit" class="add-cart-button" {{ strtolower((string) $product->availability) !== 'available' || $product->sizes->isEmpty() ? 'disabled' : '' }}>
                            Add to Cart
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="add-cart-button add-cart-link">Sign in to add to cart</a>
                @endauth
            </div>
        </div>
    </section>

    <script>
        (() => {
            const form = document.getElementById('addToCartForm');

            if (!form) {
                return;
            }

            const basePrice = Number(form.dataset.basePrice || '0');
            const totalEl = document.getElementById('totalPrice');
            const qtyInput = document.getElementById('quantity');
            const sugarInput = document.getElementById('sugarLevel');
            const iceInput = document.getElementById('iceLevel');
            const sugarLabel = document.getElementById('sugarLabel');
            const iceLabel = document.getElementById('iceLabel');
            const sizeInputs = Array.from(form.querySelectorAll('input[name="product_size_id"]'));
            const addonInputs = Array.from(form.querySelectorAll('input[name="addon_ids[]"]'));
            const minusBtn = document.getElementById('qtyMinus');
            const plusBtn = document.getElementById('qtyPlus');

            const updateTotal = () => {
                const quantity = Math.max(1, Number.parseInt(qtyInput.value || '1', 10));
                qtyInput.value = String(Math.min(20, quantity));

                const selectedSize = sizeInputs.find((input) => input.checked);
                const sizeAdjustment = selectedSize ? Number.parseFloat(selectedSize.dataset.adjustment || '0') : 0;

                const addonTotal = addonInputs.reduce((sum, input) => {
                    return input.checked ? sum + Number.parseFloat(input.dataset.price || '0') : sum;
                }, 0);

                const total = (basePrice + sizeAdjustment + addonTotal) * Number.parseInt(qtyInput.value, 10);
                totalEl.textContent = `₱${total.toFixed(2)}`;
            };

            minusBtn?.addEventListener('click', () => {
                qtyInput.value = String(Math.max(1, Number.parseInt(qtyInput.value || '1', 10) - 1));
                updateTotal();
            });

            plusBtn?.addEventListener('click', () => {
                qtyInput.value = String(Math.min(20, Number.parseInt(qtyInput.value || '1', 10) + 1));
                updateTotal();
            });

            sugarInput?.addEventListener('input', () => {
                sugarLabel.textContent = `${sugarInput.value}%`;
            });

            iceInput?.addEventListener('input', () => {
                iceLabel.textContent = `${iceInput.value}%`;
            });

            [qtyInput, ...sizeInputs, ...addonInputs].forEach((input) => {
                input.addEventListener('input', updateTotal);
                input.addEventListener('change', updateTotal);
            });

            updateTotal();
        })();
    </script>
</x-layouts.customer>
