<x-layouts.admin title="Kumachi Admin | Edit Add-on">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Edit Add-on</h1>
            <a class="products-add-button" href="{{ route('admin.addons.index') }}">Back to Add-ons</a>
        </header>

        <section class="products-form-panel">
            <form class="products-form" method="POST" action="{{ route('admin.addons.update', $addon) }}">
                @csrf
                @method('PUT')

                <div class="products-form-grid">
                    <div class="products-field">
                        <label for="name">Add-on Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $addon->name) }}" required>
                        <x-input-error :messages="$errors->get('name')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="price">Price</label>
                        <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $addon->price) }}" required>
                        <x-input-error :messages="$errors->get('price')" class="products-form-error" />
                    </div>
                </div>

                <div class="products-form-grid">
                    <div class="products-field">
                        <label for="inventory_id">Linked Inventory Item</label>
                        <select id="inventory_id" name="inventory_id" required>
                            <option value="">Select inventory item</option>
                            @foreach ($inventories as $inventory)
                                <option value="{{ $inventory->id }}" {{ (string) old('inventory_id', $addon->inventory_id) === (string) $inventory->id ? 'selected' : '' }}>
                                    {{ $inventory->item_name }}
                                    @if ($inventory->suppliers->isNotEmpty())
                                        ({{ $inventory->suppliers->pluck('supplier_name')->join(', ') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('inventory_id')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="inventory_usage_qty">Inventory Usage per Add-on Use</label>
                        <input id="inventory_usage_qty" name="inventory_usage_qty" type="number" step="0.01" min="0.01" value="{{ old('inventory_usage_qty', (float) $addon->inventory_usage_qty) }}" required>
                        <p class="checkout-promo-hint" style="margin-top: 0.35rem;">
                            Enter usage in the linked item&rsquo;s <strong>recipe / usage unit</strong> (inventory &ldquo;Recipe / Usage Unit&rdquo;), not the stock unit.
                        </p>
                        <x-input-error :messages="$errors->get('inventory_usage_qty')" class="products-form-error" />
                    </div>
                </div>

                <div class="products-field">
                    <label>Available for Products</label>
                    <div class="products-checkbox-grid">
                        @foreach ($products as $product)
                            <label class="products-checkbox-item" for="product_{{ $product->id }}">
                                <input
                                    id="product_{{ $product->id }}"
                                    type="checkbox"
                                    name="product_ids[]"
                                    value="{{ $product->id }}"
                                    {{ in_array((string) $product->id, array_map('strval', old('product_ids', $selectedProductIds)), true) ? 'checked' : '' }}
                                >
                                <span>{{ $product->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('product_ids')" class="products-form-error" />
                </div>

                <div class="products-form-actions">
                    <button class="products-add-button" type="submit">Update Add-on</button>
                </div>
            </form>
        </section>
    </section>
</x-layouts.admin>
