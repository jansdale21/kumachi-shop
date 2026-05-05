<x-layouts.admin title="Kumachi Admin | Categories">
    <section class="admin-categories-page">
        <header class="categories-head">
            <h1>Categories</h1>
            <a class="products-add-button" href="{{ route('admin.categories.create') }}">+ Add Category</a>
        </header>

        @if (session('status'))
            <p class="products-flash">{{ session('status') }}</p>
        @endif
        @if (session('error'))
            <p class="products-flash products-flash-error">{{ session('error') }}</p>
        @endif

        <section class="categories-grid" aria-label="Category list">
            @forelse ($categories as $category)
                <article class="category-card">
                    <div>
                        <h2>{{ $category->name }}</h2>
                        <p>{{ $category->products_count }} {{ \Illuminate\Support\Str::plural('product', $category->products_count) }}</p>
                    </div>

                    <div class="row-actions">
                        <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" aria-label="View category products">
                            <svg viewBox="0 0 24 24">
                                <path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6-10-6-10-6Z"></path>
                                <circle cx="12" cy="12" r="2.8"></circle>
                            </svg>
                        </a>
                        <a href="{{ route('admin.categories.edit', $category) }}" aria-label="Edit category">
                            <svg viewBox="0 0 24 24">
                                <path d="M4 20h4l10-10-4-4L4 16z"></path>
                                <path d="m12 6 4 4"></path>
                            </svg>
                        </a>
                        <form
                            method="POST"
                            action="{{ route('admin.categories.destroy', $category) }}"
                            onsubmit="return confirm('Delete this category?');"
                        >
                            @csrf
                            @method('DELETE')
                            <button class="danger" type="submit" aria-label="Delete category">
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
                </article>
            @empty
                <article class="category-card category-empty">
                    <h2>No categories yet.</h2>
                    <p>Create your first category.</p>
                </article>
            @endforelse
        </section>
    </section>
</x-layouts.admin>
