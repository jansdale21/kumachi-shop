<x-layouts.admin title="Kumachi Admin | Add Purchase Order">
    <section class="admin-products-page">
        <header class="products-head">
            <h1>Add Purchase Order</h1>
            <a class="products-add-button" href="{{ route('admin.purchase-orders.index') }}">Back to Purchase Orders</a>
        </header>

        <section class="products-form-panel">
            <form class="products-form" method="POST" action="{{ route('admin.purchase-orders.store') }}" id="poForm">
                @csrf

                <div class="products-form-grid">
                    <div class="products-field">
                        <label for="supplier_id">Supplier</label>
                        <select id="supplier_id" name="supplier_id" required>
                            <option value="">Select supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ (string) old('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->supplier_name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('supplier_id')" class="products-form-error" />
                    </div>

                    <div class="products-field">
                        <label>Total Amount (₱)</label>
                        <input id="poTotalDisplay" type="text" value="0.00" readonly>
                    </div>
                </div>

                <div class="products-field">
                    <label>Items</label>
                    <div class="products-table-panel">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th style="width: 3rem;">Add</th>
                                    <th>Item</th>
                                    <th style="width: 7rem;">Unit</th>
                                    <th style="width: 10rem;">Qty</th>
                                    <th style="width: 10rem;">Unit cost (₱)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventories as $inventory)
                                    <tr class="po-item-row" data-unit="{{ $inventory->unit }}" data-supplier-ids="{{ $inventory->suppliers->pluck('id')->implode(',') }}">
                                        <td>
                                            <input type="checkbox" class="po-item-toggle" data-inventory-id="{{ $inventory->id }}">
                                        </td>
                                        <td>{{ $inventory->item_name }}</td>
                                        <td>{{ strtoupper((string) $inventory->unit) }}</td>
                                        <td>
                                            <input
                                                type="number"
                                                min="1"
                                                step="1"
                                                class="po-qty"
                                                data-inventory-id="{{ $inventory->id }}"
                                                placeholder="0 {{ $inventory->unit }}"
                                                disabled
                                            >
                                        </td>
                                        <td>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                class="po-unit-cost"
                                                data-inventory-id="{{ $inventory->id }}"
                                                placeholder="0.00"
                                                disabled
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <x-input-error :messages="$errors->get('items')" class="products-form-error" />
                </div>

                <div class="products-form-actions">
                    <button class="products-add-button" type="submit">Save Purchase Order</button>
                </div>
            </form>
        </section>
    </section>

    <script>
        (() => {
            const form = document.getElementById('poForm');
            if (!form) return;

            const supplierSelect = form.querySelector('#supplier_id');
            const totalDisplay = form.querySelector('#poTotalDisplay');
            const toggles = Array.from(form.querySelectorAll('.po-item-toggle'));
            const qtyInputs = Array.from(form.querySelectorAll('.po-qty'));
            const unitCostInputs = Array.from(form.querySelectorAll('.po-unit-cost'));
            const rows = Array.from(form.querySelectorAll('.po-item-row'));

            const syncRow = (inventoryId, enabled) => {
                const qty = qtyInputs.find((input) => input.dataset.inventoryId === inventoryId);
                const unitCost = unitCostInputs.find((input) => input.dataset.inventoryId === inventoryId);
                if (!qty) return;
                qty.disabled = !enabled;
                if (unitCost) {
                    unitCost.disabled = !enabled;
                }
                if (!enabled) {
                    qty.value = '';
                    if (unitCost) {
                        unitCost.value = '';
                    }
                }
            };

            const calculateTotal = () => {
                let total = 0;
                toggles.forEach((toggle) => {
                    if (!toggle.checked) return;
                    const inventoryId = toggle.dataset.inventoryId;
                    const qty = qtyInputs.find((input) => input.dataset.inventoryId === inventoryId);
                    const unitCost = unitCostInputs.find((input) => input.dataset.inventoryId === inventoryId);
                    const qtyValue = qty ? Number.parseFloat(qty.value || '0') : 0;
                    const unitCostValue = unitCost ? Number.parseFloat(unitCost.value || '0') : 0;
                    total += qtyValue * unitCostValue;
                });
                totalDisplay.value = total.toFixed(2);
            };

            const filterRowsBySupplier = () => {
                const supplierId = supplierSelect ? supplierSelect.value : '';
                rows.forEach((row) => {
                    const supplierIds = (row.dataset.supplierIds || '')
                        .split(',')
                        .map((value) => value.trim())
                        .filter((value) => value !== '');
                    const isVisible = supplierId !== '' && supplierIds.includes(supplierId);

                    row.style.display = isVisible ? '' : 'none';

                    if (!isVisible) {
                        const toggle = row.querySelector('.po-item-toggle');
                        if (toggle) {
                            toggle.checked = false;
                            syncRow(toggle.dataset.inventoryId, false);
                        }
                    }
                });

                calculateTotal();
            };

            toggles.forEach((toggle) => {
                toggle.addEventListener('change', () => {
                    syncRow(toggle.dataset.inventoryId, toggle.checked);
                    calculateTotal();
                });
            });

            [...qtyInputs, ...unitCostInputs].forEach((input) => {
                input.addEventListener('input', calculateTotal);
            });

            supplierSelect?.addEventListener('change', filterRowsBySupplier);

            form.addEventListener('submit', () => {
                form.querySelectorAll('input[name^="items["]').forEach((el) => el.remove());

                let index = 0;
                toggles.forEach((toggle) => {
                    if (!toggle.checked) return;

                    const inventoryId = toggle.dataset.inventoryId;
                    const qty = qtyInputs.find((input) => input.dataset.inventoryId === inventoryId);
                    const unitCost = unitCostInputs.find((input) => input.dataset.inventoryId === inventoryId);
                    const quantityValue = qty ? Number.parseInt(qty.value || '0', 10) : 0;
                    const unitCostValue = unitCost ? Number.parseFloat(unitCost.value || '0') : 0;
                    if (!quantityValue || quantityValue < 1) return;
                    if (!unitCostValue || unitCostValue < 0) return;

                    const invHidden = document.createElement('input');
                    invHidden.type = 'hidden';
                    invHidden.name = `items[${index}][inventory_id]`;
                    invHidden.value = inventoryId;

                    const qtyHidden = document.createElement('input');
                    qtyHidden.type = 'hidden';
                    qtyHidden.name = `items[${index}][quantity]`;
                    qtyHidden.value = String(quantityValue);

                    const unitCostHidden = document.createElement('input');
                    unitCostHidden.type = 'hidden';
                    unitCostHidden.name = `items[${index}][unit_cost]`;
                    unitCostHidden.value = String(unitCostValue.toFixed(2));

                    form.appendChild(invHidden);
                    form.appendChild(qtyHidden);
                    form.appendChild(unitCostHidden);
                    index++;
                });
            });

            filterRowsBySupplier();
        })();
    </script>
</x-layouts.admin>
