<x-layouts.admin title="Kumachi Admin | Products">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Products</h1>
            <a class="products-add-button" href="{{ route('admin.products.create') }}">+ Add Product</a>
        </header>

        @if (session('status'))
            <p class="products-flash">{{ session('status') }}</p>
        @endif

        <form class="products-search" method="GET" action="{{ route('admin.products.index') }}">
            <label class="sr-only" for="productCategory">Filter by category</label>
            <select id="productCategory" name="category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <label class="sr-only" for="productSearch">Search products</label>
            <input
                id="productSearch"
                name="q"
                type="search"
                value="{{ $search }}"
                placeholder="Search products..."
                autocomplete="off"
            >

            <button type="submit">Apply</button>

            @if ($categoryId || $search !== '')
                <a class="products-clear-filter" href="{{ route('admin.products.index') }}">Clear</a>
            @endif
        </form>

        <section class="products-table-panel" aria-label="Products table">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="product-cell">
                                    @if ($product->image_path)
                                        <a
                                            class="product-image-link"
                                            href="{{ '/storage/'.$product->image_path }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="View larger image for {{ $product->name }}"
                                        >
                                            <img
                                                src="{{ '/storage/'.$product->image_path }}"
                                                alt="{{ $product->name }}"
                                                class="product-thumbnail"
                                            >
                                        </a>
                                    @else
                                        <span class="product-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24">
                                                <path d="m6 7 6-3 6 3v10l-6 3-6-3Z"></path>
                                                <path d="M6 7l6 3 6-3"></path>
                                                <path d="M12 10v10"></path>
                                            </svg>
                                        </span>
                                    @endif
                                    <span>
                                        <strong>{{ $product->name }}</strong>
                                        <small>
                                            {{ strtolower((string) $product->availability) === 'available' ? 'Available' : 'Unavailable' }}
                                        </small>
                                    </span>
                                </div>
                            </td>
                            <td>{{ $product->category?->name ?? 'Uncategorized' }}</td>
                            <td>₱{{ number_format((float) $product->base_price, 2) }}</td>
                            <td>
                                @php
                                    $isUnavailable = strtolower((string) $product->availability) !== 'available';
                                @endphp
                                <span class="status-tag {{ $isUnavailable ? 'is-unavailable' : 'is-available' }}">
                                    {{ $isUnavailable ? 'Unavailable' : 'Available' }}
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.products.edit', $product) }}" aria-label="Edit product">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M4 20h4l10-10-4-4L4 16z"></path>
                                            <path d="m12 6 4 4"></path>
                                        </svg>
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.products.destroy', $product) }}"
                                        onsubmit="return confirm('Delete this product?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit" aria-label="Delete product">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M4 7h16"></path>
                                                <path d="M10 11v6"></path>
                                                <path d="M14 11v6"></path>
                                                <path d="M6 7l1 13h10l1-13"></path>
                                                <path d="M9 7V4h6v3"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="products-empty">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </section>
</x-layouts.admin>
