<x-layouts.admin title="Kumachi Admin | Promotions">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Promotions</h1>
            <a class="products-add-button" href="{{ route('admin.promotions.create') }}">+ Add Promotion</a>
        </header>

        @if (session('status'))
            <p class="products-flash">{{ session('status') }}</p>
        @endif
        @if (session('error'))
            <p class="products-flash products-flash-error">{{ session('error') }}</p>
        @endif

        <form class="products-search" method="GET" action="{{ route('admin.promotions.index') }}">
            <label class="sr-only" for="promotionSearch">Search promotions</label>
            <input
                id="promotionSearch"
                name="q"
                type="search"
                value="{{ $search }}"
                placeholder="Search code..."
                autocomplete="off"
            >

            <button type="submit">Apply</button>

            @if ($search !== '')
                <a class="products-clear-filter" href="{{ route('admin.promotions.index') }}">Clear</a>
            @endif
        </form>

        <section class="products-table-panel" aria-label="Promotions table">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Expires</th>
                        <th>Orders Used</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($promotions as $promotion)
                        <tr>
                            <td><strong>{{ $promotion->code }}</strong></td>
                            <td>₱{{ number_format((float) $promotion->discount_value, 2) }}</td>
                            <td>{{ $promotion->expires_at?->format('Y-m-d') ?? 'No expiry' }}</td>
                            <td>{{ $promotion->orders_count }}</td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.promotions.edit', $promotion) }}" aria-label="Edit promotion">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M4 20h4l10-10-4-4L4 16z"></path>
                                            <path d="m12 6 4 4"></path>
                                        </svg>
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.promotions.destroy', $promotion) }}"
                                        onsubmit="return confirm('Delete this promotion?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button class="danger" type="submit" aria-label="Delete promotion">
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
                            <td colspan="5" class="products-empty">No promotions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </section>
</x-layouts.admin>
