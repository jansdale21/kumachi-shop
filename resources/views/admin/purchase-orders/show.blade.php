<x-layouts.admin title="Kumachi Admin | Purchase Order Details">
    <section class="admin-order-show-page">
        <header class="order-show-head">
            <div>
                <h1>Purchase Order {{ 'PO' . str_pad((string) $purchaseOrder->id, 6, '0', STR_PAD_LEFT) }}</h1>
                <p>Created on {{ $purchaseOrder->created_at?->format('Y-m-d H:i') }}</p>
            </div>
            <a class="ui-btn ui-btn-primary order-show-back" href="{{ route('admin.purchase-orders.index') }}">Back to Purchase Orders</a>
        </header>

        @if (session('status'))
            <p class="orders-flash">{{ session('status') }}</p>
        @endif

        <div class="order-show-grid">
            <section class="order-show-panel">
                <h2>Summary</h2>
                <dl class="order-summary-list">
                    <div>
                        <dt>Supplier</dt>
                        <dd>{{ $purchaseOrder->supplier?->supplier_name ?? 'Supplier removed' }}</dd>
                    </div>
                    <div>
                        <dt>Status</dt>
                        <dd>
                            <span class="status-tag is-{{ strtolower((string) $purchaseOrder->status) }}">
                                {{ ucfirst((string) $purchaseOrder->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt>Total Amount</dt>
                        <dd>₱{{ number_format((float) $purchaseOrder->total_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt>Line Items</dt>
                        <dd>{{ $purchaseOrder->items->count() }}</dd>
                    </div>
                </dl>
            </section>

            <section class="order-show-panel">
                <h2>Actions</h2>
                <form method="POST" action="{{ route('admin.purchase-orders.update', $purchaseOrder) }}">
                    @csrf
                    @method('PUT')
                    <div class="products-field">
                        <label for="poStatus">Update Status</label>
                        <select id="poStatus" name="status" {{ $purchaseOrder->status === 'received' ? 'disabled' : '' }}>
                            <option value="ordered" {{ $purchaseOrder->status === 'ordered' ? 'selected' : '' }}>Ordered</option>
                            <option value="received" {{ $purchaseOrder->status === 'received' ? 'selected' : '' }}>Received</option>
                            <option value="cancelled" {{ $purchaseOrder->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    @if ($purchaseOrder->status !== 'received')
                        <button class="ui-btn ui-btn-primary" type="submit" data-confirm="Update purchase order status? If set to received, inventory will be restocked.">
                            Update Status
                        </button>
                    @else
                        <p class="order-date">Already received. Status is now locked.</p>
                    @endif
                </form>
                <form method="POST" action="{{ route('admin.purchase-orders.destroy', $purchaseOrder) }}" data-confirm="Delete this purchase order?">
                    @csrf
                    @method('DELETE')
                    <button class="ui-btn ui-btn-danger" type="submit">
                        Delete Purchase Order
                    </button>
                </form>
                <p class="order-date" style="margin-top:0.65rem;">
                    Tip: set status to <strong>Received</strong> only after items physically arrive. This action adds stock to inventory.
                </p>
            </section>
        </div>

        <section class="order-show-panel order-items-panel">
            <h2>Items</h2>
            <div class="table-wrap">
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Inventory Item</th>
                            <th>Unit</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseOrder->items as $item)
                            <tr>
                                <td>{{ $item->inventory?->item_name ?? 'Deleted inventory item' }}</td>
                                <td>{{ strtoupper((string) ($item->unit ?? 'unit')) }}</td>
                                <td>{{ number_format((float) $item->quantity, 2) }} {{ $item->unit ?? 'unit' }}</td>
                                <td>₱{{ number_format((float) $item->unit_cost, 2) }}</td>
                                <td>₱{{ number_format((float) ($item->unit_cost * $item->quantity), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="orders-empty">No items found for this purchase order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </section>
</x-layouts.admin>
