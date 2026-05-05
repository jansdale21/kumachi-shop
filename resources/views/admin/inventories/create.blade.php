<x-layouts.admin title="Kumachi Admin | Add Inventory Item">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Add Inventory Item</h1>
            <a class="ui-btn ui-btn-secondary" href="{{ route('admin.inventories.index') }}">Back to Inventory</a>
        </header>

        <section class="products-form-panel">
            <form class="products-form" method="POST" action="{{ route('admin.inventories.store') }}">
                @csrf
                <div class="products-form-grid">
                    <div class="products-field">
                        <label for="item_name">Item name</label>
                        <input id="item_name" name="item_name" type="text" value="{{ old('item_name') }}" required>
                        <x-input-error :messages="$errors->get('item_name')" class="products-form-error" />
                    </div>
                    <div class="products-field">
                        <label for="unit">Stock unit</label>
                        <select id="unit" name="unit" required>
                            @foreach ([
                                'pcs' => 'Pieces (pcs) — bottles/cups/cases',
                                'L' => 'Liters (L)',
                                'ml' => 'Milliliters (ml)',
                                'kg' => 'Kilograms (kg)',
                                'g' => 'Grams (g)',
                                'unit' => 'Generic unit',
                            ] as $unitValue => $unitLabel)
                                <option value="{{ $unitValue }}" {{ old('unit', 'unit') === $unitValue ? 'selected' : '' }}>{{ $unitLabel }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('unit')" class="products-form-error" />
                    </div>
                    <div class="products-field">
                        <label for="quantity">On hand</label>
                        <input id="quantity" name="quantity" type="number" step="0.01" min="0" value="{{ old('quantity') }}" required>
                        <x-input-error :messages="$errors->get('quantity')" class="products-form-error" />
                    </div>
                    <div class="products-field">
                        <label for="reorder_level">Reorder at</label>
                        <input id="reorder_level" name="reorder_level" type="number" step="0.01" min="0" value="{{ old('reorder_level') }}" required>
                        <x-input-error :messages="$errors->get('reorder_level')" class="products-form-error" />
                    </div>
                    <div class="products-field">
                        <label for="base_unit">Recipe unit</label>
                        <select id="base_unit" name="base_unit" required>
                            @foreach ([
                                'ml' => 'Milliliters (ml)',
                                'L' => 'Liters (L)',
                                'g' => 'Grams (g)',
                                'kg' => 'Kilograms (kg)',
                                'pcs' => 'Pieces (pcs)',
                                'unit' => 'Generic unit',
                            ] as $unitValue => $unitLabel)
                                <option value="{{ $unitValue }}" {{ old('base_unit', old('unit', 'unit')) === $unitValue ? 'selected' : '' }}>{{ $unitLabel }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('base_unit')" class="products-form-error" />
                    </div>
                    <div class="products-field">
                        <label for="units_per_stock_unit">Per 1 stock unit</label>
                        <input id="units_per_stock_unit" name="units_per_stock_unit" type="number" step="0.0001" min="0.0001" value="{{ old('units_per_stock_unit', 1) }}" required>
                        <x-input-error :messages="$errors->get('units_per_stock_unit')" class="products-form-error" />
                    </div>
                </div>

                <div class="products-form-actions">
                    <button class="products-add-button" type="submit">Save</button>
                </div>
            </form>
        </section>
    </section>
</x-layouts.admin>
