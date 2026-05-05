<x-layouts.customer title="Kumachi | Home">
    <section class="hero-banner hero-banner--gif" aria-label="Kumachi hero banner">
        <div class="hero-banner-media" role="img" aria-label="Kumachi coffee shop"></div>
    </section>

    <section class="content-section">
        <div class="section-heading">
            <h2>Why customers choose Kumachi</h2>
            <p>A clean coffee experience with fast service, quality drinks, and real rewards.</p>
        </div>

        <div class="card-grid card-grid-3 why-grid">
            <article class="info-card spotlight-card">
                <span class="icon-badge" aria-hidden="true">
                    <svg class="icon-svg" viewBox="0 0 24 24">
                        <path d="M7 5h8"></path>
                        <path d="M7 8v5a5 5 0 0 0 5 5 5 5 0 0 0 5-5V8"></path>
                        <path d="M15 9h1.2a2.8 2.8 0 1 1 0 5.6H15"></path>
                    </svg>
                </span>
                <h3>Premium Quality</h3>
                <p>Ethically sourced beans, expertly roasted, brewed with care.</p>
            </article>
            <article class="info-card spotlight-card">
                <span class="icon-badge" aria-hidden="true">
                    <svg class="icon-svg" viewBox="0 0 24 24">
                        <path d="M13 3 5 14h6l-1 7 9-12h-6l1-6Z"></path>
                    </svg>
                </span>
                <h3>Fast Service</h3>
                <p>Quick ordering and pickup, or convenient delivery.</p>
            </article>
            <article class="info-card spotlight-card">
                <span class="icon-badge" aria-hidden="true">
                    <svg class="icon-svg" viewBox="0 0 24 24">
                        <path d="M12 5a2.3 2.3 0 1 0-4.1 1.4L12 10l4.1-3.6A2.3 2.3 0 1 0 12 5Z"></path>
                        <path d="M5 10h14v4H5z"></path>
                        <path d="M7 14v5h10v-5"></path>
                        <path d="M12 10v9"></path>
                    </svg>
                </span>
                <h3>Rewards Program</h3>
                <p>Earn points with every purchase, redeem for free drinks.</p>
            </article>
        </div>
    </section>

    <section class="content-section cream-band featured-drinks-section">
        <div class="section-heading centered featured-header">
            <div>
                <p class="featured-kicker-line">
                    <span>Featured Drinks</span>
                </p>
                <p class="featured-subtext">Our most popular handcrafted beverages</p>
            </div>
        </div>

        <div class="card-grid featured-grid">
            @forelse ($featuredProducts as $product)
                <a href="{{ route('products.show', $product) }}" class="product-link">
                    <article class="product-card">
                        @if ($product->image_path)
                            <div class="product-art">
                                <img
                                    src="{{ '/storage/'.$product->image_path }}"
                                    alt="{{ $product->name }}"
                                    class="product-image"
                                    style="width: 100%; height: 100%; object-fit: cover;"
                                >
                            </div>
                        @else
                            <div class="product-art">{{ $product->category?->name ?? 'Product' }}</div>
                        @endif
                        <div class="product-body">
                            <h3>{{ $product->name }}</h3>
                            <p>{{ $product->category?->name ?? 'Uncategorized' }}</p>
                            <div class="product-footer">
                                <strong>₱{{ number_format((float) $product->base_price, 2) }}</strong>
                                <span>→</span>
                            </div>
                        </div>
                    </article>
                </a>
            @empty
                <article class="product-card">
                    <div class="product-art">Coffee</div>
                    <div class="product-body">
                        <h3>No Products Available</h3>
                        <p>Check back soon for new products.</p>
                        <div class="product-footer">
                            <strong>—</strong>
                            <span>→</span>
                        </div>
                    </div>
                </article>
            @endforelse
        </div>

        <div class="center-action">
            <a class="button button-primary" href="{{ route('menu') }}">
                <span>View Full Menu</span>
                <span class="button-arrow" aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    <section class="content-section reward-banner rewards-strip">
        <div class="section-heading centered rewards-strip-header">
            <div>
                <h2>Join Our Rewards Program</h2>
                <p>Earn 1 point for every dollar spent. Redeem 100 points for a free drink.</p>
            </div>
        </div>
        <div class="center-action">
            <a class="button button-primary" href="{{ route('rewards') }}">
                <span>Learn More</span>
                <span class="button-arrow" aria-hidden="true">→</span>
            </a>
        </div>
    </section>
</x-layouts.customer>
