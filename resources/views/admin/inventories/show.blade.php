<x-layouts.admin title="Kumachi Admin | Inventory Details">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>{{ $inventory->item_name }}</h1>
            <a class="ui-btn ui-btn-primary" href="{{ route('admin.inventories.index') }}">Back to Inventory</a>
        </header>

        <section class="products-form-panel supplier-detail-panel">
            <div class="supplier-detail-grid">
                <div>
                    <strong>Unit</strong>
                    <p>{{ strtoupper((string) $inventory->unit) }}</p>
                </div>
                <div>
                    <strong>Quantity</strong>
                    <p>{{ number_format((float) $inventory->quantity, 2) }} {{ $inventory->unit }}</p>
                </div>
                <div>
                    <strong>Reorder Level</strong>
                    <p>{{ number_format((float) $inventory->reorder_level, 2) }} {{ $inventory->unit }}</p>
                </div>
                <div class="supplier-detail-wide">
                    <strong>Suppliers</strong>
                    <div class="supplier-tags">
                        @forelse ($inventory->suppliers as $supplier)
                            <span class="supplier-tag">{{ $supplier->supplier_name }}</span>
                        @empty
                            <span class="supplier-tag supplier-tag-empty">No linked suppliers</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </section>
</x-layouts.admin>
