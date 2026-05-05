<x-layouts.admin title="Kumachi Admin | Add Supplier">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Add Supplier</h1>
            <a class="ui-btn ui-btn-secondary" href="{{ route('admin.suppliers.index') }}">Back to Suppliers</a>
        </header>

        <section class="products-form-panel">
            <form class="products-form" method="POST" action="{{ route('admin.suppliers.store') }}">
                @csrf

                <div class="products-form-grid">
                    <div class="products-field">
                        <label for="supplier_name">Supplier Name</label>
                        <input id="supplier_name" name="supplier_name" type="text" value="{{ old('supplier_name') }}" required>
                        <x-input-error :messages="$errors->get('supplier_name')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="contact_person">Contact Person</label>
                        <input id="contact_person" name="contact_person" type="text" value="{{ old('contact_person') }}">
                        <x-input-error :messages="$errors->get('contact_person')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}">
                        <x-input-error :messages="$errors->get('email')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}">
                        <x-input-error :messages="$errors->get('phone')" class="products-form-error" />
                    </div>
                </div>

                <div class="products-field">
                    <label for="address">Address</label>
                    <input id="address" name="address" type="text" value="{{ old('address') }}">
                    <x-input-error :messages="$errors->get('address')" class="products-form-error" />
                </div>

                <div class="products-field">
                    <label for="inventory_ids">Supplies (Inventory Items)</label>
                    <div class="products-checkbox-grid">
                        @foreach ($inventories as $inventory)
                            <label class="products-checkbox-item" for="inventory_{{ $inventory->id }}">
                                <input
                                    id="inventory_{{ $inventory->id }}"
                                    type="checkbox"
                                    name="inventory_ids[]"
                                    value="{{ $inventory->id }}"
                                    {{ in_array((string) $inventory->id, array_map('strval', old('inventory_ids', [])), true) ? 'checked' : '' }}
                                >
                                <span>{{ $inventory->item_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('inventory_ids')" class="products-form-error" />
                </div>

                <div class="products-field">
                    <label for="new_supplies">Add Supplies (one item per line)</label>
                    <textarea id="new_supplies" name="new_supplies" rows="4" placeholder="e.g. Tapioca Pearls&#10;Oat Milk">{{ old('new_supplies') }}</textarea>
                    <x-input-error :messages="$errors->get('new_supplies')" class="products-form-error" />
                </div>

                <div class="products-field">
                    <label for="is_active">Status</label>
                    <select id="is_active" name="is_active">
                        <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="products-form-actions">
                    <button class="products-add-button" type="submit">Save Supplier</button>
                </div>
            </form>
        </section>
    </section>
</x-layouts.admin>
