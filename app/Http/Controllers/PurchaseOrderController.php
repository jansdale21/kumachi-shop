<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceivePurchaseOrderRequest;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $status = strtolower(trim((string) $request->query('status', '')));
        $search = trim((string) $request->query('q', ''));

        $purchaseOrders = PurchaseOrder::query()
            ->with(['supplier', 'items'])
            ->when($status !== '' && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('id', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('supplier_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        $statuses = [
            'pending' => 'Pending',
            'ordered' => 'Ordered',
            'received' => 'Received',
            'cancelled' => 'Cancelled',
        ];

        return view('admin.purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'statuses' => $statuses,
            'currentStatus' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->with(['inventoryItems:id,item_name,unit'])
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_name']);

        $inventories = Inventory::query()
            ->with(['suppliers:id'])
            ->orderBy('item_name')
            ->get(['id', 'item_name', 'unit']);

        return view('admin.purchase-orders.create', [
            'suppliers' => $suppliers,
            'inventories' => $inventories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $purchaseOrder = DB::transaction(function () use ($validated) {
            $totalAmount = collect($validated['items'])->sum(function (array $item): float {
                return (float) $item['quantity'] * (float) $item['unit_cost'];
            });

            $inventoryUnits = Inventory::query()
                ->whereIn('id', collect($validated['items'])->pluck('inventory_id')->all())
                ->pluck('unit', 'id');

            $purchaseOrder = PurchaseOrder::query()->create([
                'supplier_id' => $validated['supplier_id'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $purchaseOrder->items()->create([
                    'inventory_id' => $item['inventory_id'],
                    'unit' => (string) ($inventoryUnits[(int) $item['inventory_id']] ?? 'unit'),
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);
            }

            return $purchaseOrder;
        });

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('status', 'Purchase order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'items.inventory']);

        return view('admin.purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(ReceivePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $targetStatus = (string) $request->validated('status');
        $currentStatus = (string) $purchaseOrder->status;

        if ($currentStatus === 'received' && $targetStatus !== 'received') {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('status', 'Received purchase orders cannot be changed.');
        }

        if ($targetStatus === $currentStatus) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('status', 'Purchase order status is already up to date.');
        }

        DB::transaction(function () use ($purchaseOrder, $targetStatus, $currentStatus) {
            if ($targetStatus === 'received' && $currentStatus !== 'received') {
                $purchaseOrder->load('items');

                foreach ($purchaseOrder->items as $item) {
                    Inventory::query()
                        ->whereKey($item->inventory_id)
                        ->increment('quantity', $item->quantity);

                    InventoryTransaction::query()->create([
                        'inventory_id' => $item->inventory_id,
                        'transaction_type' => 'stock_in',
                        'quantity' => $item->quantity,
                    ]);
                }
            }

            $purchaseOrder->update([
                'status' => $targetStatus,
            ]);
        });

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('status', 'Purchase order status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->delete();

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('status', 'Purchase order deleted successfully.');
    }
}
