<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddonRequest;
use App\Http\Requests\UpdateAddonRequest;
use App\Models\Addon;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->string('q');

        $addons = Addon::query()
            ->with(['inventory.suppliers'])
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();

        return view('admin.addons.index', [
            'addons' => $addons,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        $inventories = Inventory::query()
            ->with(['suppliers:id,supplier_name'])
            ->orderBy('item_name')
            ->get(['id', 'item_name']);

        return view('admin.addons.create', [
            'products' => $products,
            'inventories' => $inventories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAddonRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $addon = Addon::query()->create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'inventory_id' => $validated['inventory_id'],
            'inventory_usage_qty' => $validated['inventory_usage_qty'],
        ]);

        $addon->products()->sync($validated['product_ids'] ?? []);

        return redirect()
            ->route('admin.addons.index')
            ->with('status', 'Add-on created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Addon $addon): View
    {
        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name']);
        $inventories = Inventory::query()
            ->with(['suppliers:id,supplier_name'])
            ->orderBy('item_name')
            ->get(['id', 'item_name']);

        $selectedProductIds = $addon->products()
            ->pluck('products.id')
            ->all();

        return view('admin.addons.edit', [
            'addon' => $addon,
            'products' => $products,
            'inventories' => $inventories,
            'selectedProductIds' => $selectedProductIds,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAddonRequest $request, Addon $addon): RedirectResponse
    {
        $validated = $request->validated();

        $addon->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'inventory_id' => $validated['inventory_id'],
            'inventory_usage_qty' => $validated['inventory_usage_qty'],
        ]);

        $addon->products()->sync($validated['product_ids'] ?? []);

        return redirect()
            ->route('admin.addons.index')
            ->with('status', 'Add-on updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Addon $addon): RedirectResponse
    {
        $addon->delete();

        return redirect()
            ->route('admin.addons.index')
            ->with('status', 'Add-on deleted successfully.');
    }
}
