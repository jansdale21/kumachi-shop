<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Inventory;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->string('q');

        $suppliers = Supplier::query()
            ->with('inventoryItems')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('supplier_name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('supplier_name')
            ->get();

        return view('admin.suppliers.index', [
            'suppliers' => $suppliers,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $inventories = Inventory::query()
            ->orderBy('item_name')
            ->get(['id', 'item_name']);

        return view('admin.suppliers.create', [
            'inventories' => $inventories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier = Supplier::query()->create([
            'supplier_name' => $validated['supplier_name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        $inventoryIds = collect($validated['inventory_ids'] ?? [])
            ->map(static fn ($id) => (int) $id)
            ->all();
        $newSupplyInventoryIds = $this->createSupplyInventoryIdsFromInput((string) ($validated['new_supplies'] ?? ''));
        $supplier->inventoryItems()->sync(array_values(array_unique(array_merge($inventoryIds, $newSupplyInventoryIds))));

        return redirect()
            ->route('admin.suppliers.index')
            ->with('status', 'Supplier created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier): View
    {
        $supplier->load('inventoryItems');

        return view('admin.suppliers.show', [
            'supplier' => $supplier,
            'availableInventories' => Inventory::query()->orderBy('item_name')->get(['id', 'item_name']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier): View
    {
        $inventories = Inventory::query()
            ->orderBy('item_name')
            ->get(['id', 'item_name']);

        return view('admin.suppliers.edit', [
            'supplier' => $supplier,
            'inventories' => $inventories,
            'selectedInventoryIds' => $supplier->inventoryItems()->pluck('inventories.id')->all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $supplier->update([
            'supplier_name' => $validated['supplier_name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        $inventoryIds = collect($validated['inventory_ids'] ?? [])
            ->map(static fn ($id) => (int) $id)
            ->all();
        $newSupplyInventoryIds = $this->createSupplyInventoryIdsFromInput((string) ($validated['new_supplies'] ?? ''));
        $supplier->inventoryItems()->sync(array_values(array_unique(array_merge($inventoryIds, $newSupplyInventoryIds))));

        return redirect()
            ->route('admin.suppliers.index')
            ->with('status', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('status', 'Supplier deleted successfully.');
    }

    /**
     * @return array<int, int>
     */
    private function createSupplyInventoryIdsFromInput(string $newSupplies): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $newSupplies) ?: [];

        return collect($lines)
            ->map(static fn ($line) => trim((string) $line))
            ->filter()
            ->unique()
            ->map(function (string $name): int {
                return (int) Inventory::query()->firstOrCreate(
                    ['item_name' => $name],
                    [
                        'unit' => 'unit',
                        'base_unit' => 'unit',
                        'units_per_stock_unit' => 1,
                        'quantity' => 0,
                        'reorder_level' => 1,
                    ]
                )->id;
            })
            ->values()
            ->all();
    }
}
