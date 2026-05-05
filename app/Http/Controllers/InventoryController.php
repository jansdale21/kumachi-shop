<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use App\Models\Inventory;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->string('q');

        $inventories = Inventory::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('item_name', 'like', "%{$search}%");
            })
            ->orderBy('item_name')
            ->get();

        return view('admin.inventories.index', [
            'inventories' => $inventories,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.inventories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryRequest $request): RedirectResponse
    {
        $inventory = Inventory::query()->create($request->validated());
        $this->notifyAdminsIfLowStock($inventory, null);

        return redirect()
            ->route('admin.inventories.index')
            ->with('status', 'Inventory item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory): View
    {
        $inventory->load('suppliers');

        return view('admin.inventories.show', [
            'inventory' => $inventory,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventory $inventory): View
    {
        return view('admin.inventories.edit', [
            'inventory' => $inventory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryRequest $request, Inventory $inventory): RedirectResponse
    {
        $previousQuantity = (float) $inventory->quantity;
        $previousReorderLevel = (float) $inventory->reorder_level;
        $previousUnitsPerStock = (float) $inventory->units_per_stock_unit;

        $inventory->update($request->validated());
        $inventory->refresh();

        $previousOnHandBase = $previousQuantity * $previousUnitsPerStock;
        $previousReorderBase = $previousReorderLevel * $previousUnitsPerStock;
        $this->notifyAdminsIfLowStock($inventory, [
            'on_hand_base' => $previousOnHandBase,
            'reorder_base' => $previousReorderBase,
        ]);

        return redirect()
            ->route('admin.inventories.index')
            ->with('status', 'Inventory item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory): RedirectResponse
    {
        $inventory->delete();

        return redirect()
            ->route('admin.inventories.index')
            ->with('status', 'Inventory item deleted successfully.');
    }

    /**
     * @param  array{on_hand_base: float, reorder_base: float}|null  $previousState
     */
    private function notifyAdminsIfLowStock(Inventory $inventory, ?array $previousState): void
    {
        $currentOnHandBase = $inventory->quantityInBaseUnits();
        $currentReorderBase = $inventory->reorderLevelInBaseUnits();
        $currentIsOutOfStock = $currentOnHandBase <= 0;
        $currentIsLow = $currentOnHandBase <= $currentReorderBase;
        if (! $currentIsLow) {
            return;
        }

        if ($previousState !== null) {
            $previousOnHandBase = (float) ($previousState['on_hand_base'] ?? 0);
            $previousReorderBase = (float) ($previousState['reorder_base'] ?? 0);
            $previousIsOutOfStock = $previousOnHandBase <= 0;
            $previousIsLow = $previousOnHandBase <= $previousReorderBase;

            // Avoid repeated alerts for unchanged inventory state.
            if (
                ($currentIsOutOfStock && $previousIsOutOfStock)
                || (! $currentIsOutOfStock && $currentIsLow && ! $previousIsOutOfStock && $previousIsLow)
            ) {
                return;
            }
        }

        $adminRoleId = Role::query()->where('role_name', 'admin')->value('id');
        if (! $adminRoleId) {
            return;
        }

        $adminIds = User::query()->where('role_id', $adminRoleId)->pluck('id');
        foreach ($adminIds as $adminId) {
            Notification::query()->create([
                'user_id' => $adminId,
                'message' => $currentIsOutOfStock
                    ? "No stock alert: {$inventory->item_name} is out of stock."
                    : "Low stock alert: {$inventory->item_name} is at/below reorder level.",
                'link' => route('admin.inventories.index'),
            ]);
        }
    }
}
