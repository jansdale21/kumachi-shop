<x-layouts.admin title="Kumachi Admin | Add Category">
    <section class="admin-categories-page">
        <header class="categories-head">
            <h1>Add Category</h1>
            <a class="products-add-button" href="{{ route('admin.categories.index') }}">Back to Categories</a>
        </header>

        <section class="products-form-panel">
            <form class="products-form" method="POST" action="{{ route('admin.categories.store') }}">
                @csrf

                <div class="products-field">
                    <label for="name">Category Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                    <x-input-error :messages="$errors->get('name')" class="products-form-error" />
                </div>

                <div class="products-form-actions">
                    <button class="products-add-button" type="submit">Save Category</button>
                </div>
            </form>
        </section>
    </section>
</x-layouts.admin>
