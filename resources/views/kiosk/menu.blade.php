<x-layouts.kiosk title="Kumachi | Kiosk Menu">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/customer/menu.css') }}">
    @endpush

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/kiosk/pos.css') }}">
    @endpush

    <section class="pos-shell">
        <div class="pos-left">
            <div class="menu-shell">
                <div class="menu-header">
                    <h1>Kiosk Menu</h1>
                    <p>Tap an item to customize and add to cart.</p>
                </div>

                <div class="menu-search">
                    <input id="menuSearch" type="text" placeholder="Search menu..." aria-label="Search menu">
                </div>

                <div class="menu-categories" id="menuCategories">
                    <button class="menu-chip is-active" type="button" data-category="all">All</button>
                    @foreach ($categories as $category)
                        <button class="menu-chip" type="button" data-category="{{ $category->id }}">{{ $category->name }}</button>
                    @endforeach
                </div>

                <section class="menu-section">
                    <h2>Available</h2>
                    <div class="menu-grid" id="menuGridAvailable">
                        @foreach ($products->where('availability', 'available') as $product)
                            <a
                                href="{{ route('kiosk.products.show', $product) }}"
                                class="product-link menu-item"
                                data-category="{{ $product->category_id }}"
                                data-search="{{ strtolower($product->name . ' ' . ($product->category?->name ?? '')) }}"
                            >
                                <article class="product-card menu-card">
                                    @if ($product->image_path)
                                        <div class="product-art" style="overflow: hidden;">
                                            <img
                                                src="{{ '/storage/'.$product->image_path }}"
                                                alt="{{ $product->name }}"
                                                style="width: 100%; height: 100%; object-fit: cover;"
                                            >
                                        </div>
                                    @else
                                        <div class="product-art">{{ $product->category?->name ?? 'PRODUCT' }}</div>
                                    @endif
                                    <div class="product-body">
                                        <span class="category-label">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                                        <h3>{{ $product->name }}</h3>
                                        <p class="menu-stock menu-stock-available">Available</p>
                                        <div class="product-footer"><strong>₱{{ number_format((float) $product->base_price, 2) }}</strong><span>Customize</span></div>
                                    </div>
                                </article>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="menu-section" id="unavailableSection">
                    <h2>Unavailable</h2>
                    <div class="menu-grid" id="menuGridUnavailable">
                        @foreach ($products->where('availability', 'unavailable') as $product)
                            <div
                                class="product-link product-link-disabled menu-item"
                                aria-disabled="true"
                                data-category="{{ $product->category_id }}"
                                data-search="{{ strtolower($product->name . ' ' . ($product->category?->name ?? '')) }}"
                            >
                                <article class="product-card menu-card menu-card-disabled">
                                    @if ($product->image_path)
                                        <div class="product-art" style="overflow: hidden;">
                                            <img
                                                src="{{ '/storage/'.$product->image_path }}"
                                                alt="{{ $product->name }}"
                                                style="width: 100%; height: 100%; object-fit: cover;"
                                            >
                                        </div>
                                    @else
                                        <div class="product-art">{{ $product->category?->name ?? 'PRODUCT' }}</div>
                                    @endif
                                    <div class="product-body">
                                        <span class="category-label">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                                        <h3>{{ $product->name }}</h3>
                                        <p class="menu-stock menu-stock-unavailable">Unavailable</p>
                                        <div class="product-footer"><strong>₱{{ number_format((float) $product->base_price, 2) }}</strong><span>Unavailable</span></div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </section>

                <p class="menu-empty" id="menuEmpty">No menu items found. Try another keyword or category.</p>
            </div>
        </div>

        <div class="pos-right">
            @include('kiosk.partials.cart-panel', ['cart' => $cart, 'summary' => $summary])
        </div>

        <script>
            (() => {
                const searchInput = document.getElementById('menuSearch');
                const categoryButtons = Array.from(document.querySelectorAll('#menuCategories .menu-chip'));
                const availableItems = Array.from(document.querySelectorAll('#menuGridAvailable .menu-item'));
                const unavailableItems = Array.from(document.querySelectorAll('#menuGridUnavailable .menu-item'));
                const items = [...availableItems, ...unavailableItems];
                const emptyState = document.getElementById('menuEmpty');
                const unavailableSection = document.getElementById('unavailableSection');

                if (!searchInput || items.length === 0) {
                    return;
                }

                let activeCategory = 'all';

                const runFilter = () => {
                    const keyword = searchInput.value.trim().toLowerCase();
                    let visibleCount = 0;
                    let visibleUnavailableCount = 0;

                    items.forEach((item) => {
                        const matchesCategory = activeCategory === 'all' || item.dataset.category === activeCategory;
                        const haystack = (item.dataset.search || '').toLowerCase();
                        const matchesKeyword = keyword.length === 0 || haystack.includes(keyword);
                        const isVisible = matchesCategory && matchesKeyword;

                        item.classList.toggle('is-hidden', !isVisible);

                        if (isVisible) {
                            visibleCount++;
                            if (unavailableItems.includes(item)) {
                                visibleUnavailableCount++;
                            }
                        }
                    });

                    emptyState.classList.toggle('is-visible', visibleCount === 0);
                    if (unavailableSection) {
                        unavailableSection.classList.toggle('is-hidden', visibleUnavailableCount === 0);
                    }
                };

                searchInput.addEventListener('input', runFilter);

                categoryButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        activeCategory = button.dataset.category || 'all';
                        categoryButtons.forEach((chip) => {
                            chip.classList.toggle('is-active', chip === button);
                        });
                        runFilter();
                    });
                });
            })();
        </script>
    </section>
</x-layouts.kiosk>

