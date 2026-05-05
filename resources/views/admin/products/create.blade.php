<x-layouts.admin title="Kumachi Admin | Add Product">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Add Product</h1>
            <a class="products-add-button" href="{{ route('admin.products.index') }}">Back to Products</a>
        </header>

        <section class="products-form-panel">
            <form class="products-form" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="products-form-grid">
                    <div class="products-field">
                        <label for="name">Product Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                        <x-input-error :messages="$errors->get('name')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="image">Product Image</label>
                        <input id="image" name="image" type="file" accept="image/*" required>
                        <x-input-error :messages="$errors->get('image')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="base_price">Price</label>
                        <input id="base_price" name="base_price" type="number" step="0.01" min="0" value="{{ old('base_price') }}" required>
                        <x-input-error :messages="$errors->get('base_price')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="availability">Availability</label>
                        <select id="availability" name="availability" required>
                            <option value="available" @selected(old('availability', 'available') === 'available')>
                                Available
                            </option>
                            <option value="unavailable" @selected(old('availability') === 'unavailable')>
                                Unavailable
                            </option>
                        </select>
                        <x-input-error :messages="$errors->get('availability')" class="products-form-error" />
                    </div>
                </div>

                <div class="products-form-actions">
                    <button class="products-add-button" type="submit">Save Product</button>
                </div>
            </form>
        </section>
    </section>
</x-layouts.admin>
