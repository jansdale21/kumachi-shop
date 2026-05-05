<x-layouts.admin title="Kumachi Admin | Supplier Details">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>{{ $supplier->supplier_name }}</h1>
            <a class="ui-btn ui-btn-primary" href="{{ route('admin.suppliers.index') }}">Back to Suppliers</a>
        </header>

        <section class="products-form-panel supplier-detail-panel">
            <div class="supplier-detail-grid">
                <div>
                    <strong>Contact Person</strong>
                    <p>{{ $supplier->contact_person ?: 'N/A' }}</p>
                </div>
                <div>
                    <strong>Email</strong>
                    <p>{{ $supplier->email ?: 'N/A' }}</p>
                </div>
                <div>
                    <strong>Phone</strong>
                    <p>{{ $supplier->phone ?: 'N/A' }}</p>
                </div>
                <div>
                    <strong>Status</strong>
                    <p>{{ $supplier->is_active ? 'Active' : 'Inactive' }}</p>
                </div>
                <div class="supplier-detail-wide">
                    <strong>Address</strong>
                    <p>{{ $supplier->address ?: 'N/A' }}</p>
                </div>
                <div class="supplier-detail-wide">
                    <strong>Supplies</strong>
                    <div class="supplier-tags">
                        @forelse ($supplier->inventoryItems as $inventoryItem)
                            <span class="supplier-tag">{{ $inventoryItem->item_name }}</span>
                        @empty
                            <span class="supplier-tag supplier-tag-empty">No linked inventory items</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <form class="products-form" method="POST" action="{{ route('admin.suppliers.update', $supplier) }}" style="margin-top: 1.25rem;">
                @csrf
                @method('PUT')

                <input type="hidden" name="supplier_name" value="{{ $supplier->supplier_name }}">
                <input type="hidden" name="contact_person" value="{{ $supplier->contact_person }}">
                <input type="hidden" name="email" value="{{ $supplier->email }}">
                <input type="hidden" name="phone" value="{{ $supplier->phone }}">
                <input type="hidden" name="address" value="{{ $supplier->address }}">
                <input type="hidden" name="is_active" value="{{ (int) $supplier->is_active }}">
                @foreach ($supplier->inventoryItems as $inventoryItem)
                    <input type="hidden" name="inventory_ids[]" value="{{ $inventoryItem->id }}">
                @endforeach

                <div class="products-field">
                    <label for="new_supplies">Add Supplies (one item per line)</label>
                    <textarea id="new_supplies" name="new_supplies" rows="4" placeholder="e.g. Fresh Milk&#10;Creamer"></textarea>
                </div>

                <div class="products-form-actions">
                    <button class="products-add-button" type="submit">Add Supplies</button>
                </div>
            </form>
        </section>
    </section>
</x-layouts.admin>
