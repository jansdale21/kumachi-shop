<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KioskOrderController extends Controller
{
    public function checkout(): View|RedirectResponse
    {
        $user = request()->user();

        $cart = Cart::query()
            ->firstOrCreate(['user_id' => $user?->id]);

        $cart->load(['items.product', 'items.size', 'items.addons']);

        if ($cart->items->isEmpty()) {
            return redirect()
                ->route('kiosk.cart.index')
                ->with('status', 'Your cart is empty.');
        }

        $availablePromotions = Promotion::query()
            ->whereNotNull('code')
            ->orderBy('code')
            ->get();

        return view('kiosk.checkout', [
            'cart' => $cart,
            'summary' => $this->calculateSummary($cart->items),
            'availablePromotions' => $availablePromotions,
        ]);
    }

    public function placeOrder(PlaceOrderRequest $request): RedirectResponse
    {
        $user = request()->user();

        $cart = Cart::query()
            ->where('user_id', $user?->id)
            ->with(['items.product', 'items.size', 'items.addons'])
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('kiosk.cart.index')
                ->with('status', 'Your cart is empty.');
        }

        $user = request()->user();
        $validated = $request->validated();
        $promotion = $this->resolvePromotion($validated['promo_code'] ?? null);
        $validated['order_type'] = 'pickup';

        try {
            $order = DB::transaction(function () use ($validated, $cart, $promotion, $user) {
                $inventoryAlerts = [];
                $addonInventoryUsage = [];

                foreach ($cart->items as $cartItem) {
                    $product = Product::query()
                        ->lockForUpdate()
                        ->find($cartItem->product_id);

                    if (! $product) {
                        throw new \RuntimeException('Product not found.');
                    }

                    if (strtolower((string) $product->availability) !== 'available') {
                        throw new \RuntimeException("This product is currently unavailable: {$product->name}.");
                    }

                    foreach ($cartItem->addons as $addon) {
                        $inventoryId = (int) ($addon->inventory_id ?? 0);
                        if ($inventoryId <= 0) {
                            continue;
                        }

                        $usagePerAddon = (float) ($addon->inventory_usage_qty ?? 1);
                        $requiredQty = $usagePerAddon * (int) $cartItem->quantity;
                        $addonInventoryUsage[$inventoryId] = ($addonInventoryUsage[$inventoryId] ?? 0) + $requiredQty;
                    }
                }

                foreach ($addonInventoryUsage as $inventoryId => $requiredBase) {
                    $inventory = Inventory::query()
                        ->lockForUpdate()
                        ->find($inventoryId);

                    if (! $inventory) {
                        throw new \RuntimeException('Linked inventory item for an add-on was not found.');
                    }

                    if ($inventory->quantityInBaseUnits() < (float) $requiredBase) {
                        throw new \RuntimeException("Insufficient inventory stock for {$inventory->item_name}.");
                    }
                }

                $summary = $this->calculateSummary($cart->items, $promotion);
                $addressId = null;

                $paymentStatus = ($validated['payment_method'] ?? 'cash') === 'cash' ? 'pending' : 'paid';

                $order = Order::query()->create([
                    'user_id' => $user?->id,
                    'address_id' => $addressId,
                    'promotion_id' => $promotion?->id,
                    'scheduled_for' => $validated['scheduled_for'] ?? null,
                    'order_type' => $validated['order_type'] ?? 'pickup',
                    'order_source' => 'kiosk',
                    'status' => 'pending',
                    'payment_status' => $paymentStatus,
                    'total_amount' => $summary['total'],
                ]);

                foreach ($cart->items as $cartItem) {
                    $addonTotal = $cartItem->addons->sum(fn ($addon) => (float) $addon->price);
                    $unitPrice = (float) $cartItem->product->base_price
                        + (float) ($cartItem->size?->price_adjustment ?? 0)
                        + $addonTotal;

                    $orderItem = $order->items()->create([
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $unitPrice,
                    ]);

                    foreach ($cartItem->addons as $addon) {
                        $orderItem->addons()->create([
                            'addon_name' => $addon->name,
                            'addon_price' => $addon->price,
                        ]);
                    }
                }

                foreach ($addonInventoryUsage as $inventoryId => $requiredBase) {
                    $inventoryRow = Inventory::query()
                        ->lockForUpdate()
                        ->find($inventoryId);

                    if (! $inventoryRow) {
                        throw new \RuntimeException('Linked inventory item for an add-on was not found.');
                    }

                    $stockOut = $inventoryRow->baseUnitsToStockUnits((float) $requiredBase);

                    Inventory::query()
                        ->whereKey($inventoryId)
                        ->decrement('quantity', $stockOut);

                    InventoryTransaction::query()->create([
                        'inventory_id' => $inventoryId,
                        'transaction_type' => 'stock_out',
                        'quantity' => $stockOut,
                    ]);

                    $updatedInventory = Inventory::query()->find($inventoryId);
                    if (
                        $updatedInventory
                        && $updatedInventory->quantityInBaseUnits() <= $updatedInventory->reorderLevelInBaseUnits()
                    ) {
                        $inventoryAlerts[] = $updatedInventory->quantityInBaseUnits() <= 0
                            ? "No stock alert: {$updatedInventory->item_name} is out of stock."
                            : "Low stock alert: {$updatedInventory->item_name} is at/below reorder level.";
                    }
                }

                Payment::query()->create([
                    'order_id' => $order->id,
                    'payment_method' => $validated['payment_method'],
                    'amount' => $summary['total'],
                    'status' => $paymentStatus,
                ]);

                // Cart cleanup after successful order should fully clear transient cart rows.
                $cart->items()->forceDelete();

                $adminRoleId = Role::query()->where('role_name', 'admin')->value('id');
                if ($adminRoleId) {
                    $adminIds = User::query()->where('role_id', $adminRoleId)->pluck('id');

                    foreach ($adminIds as $adminId) {
                        Notification::query()->create([
                            'user_id' => $adminId,
                            'message' => 'New kiosk order '.sprintf('KM%06d', (int) $order->id).' is pending.',
                            'link' => route('admin.orders.show', $order),
                        ]);
                    }

                    foreach (array_unique($inventoryAlerts) as $inventoryAlertMessage) {
                        foreach ($adminIds as $adminId) {
                            Notification::query()->create([
                                'user_id' => $adminId,
                                'message' => $inventoryAlertMessage,
                                'link' => route('admin.inventories.index'),
                            ]);
                        }
                    }
                }

                return $order;
            });
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('kiosk.cart.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('kiosk.orders.receipt', $order)
            ->with('status', 'Order placed successfully.');
    }

    public function receipt(Order $order): View
    {
        abort_if($order->user_id !== request()->user()?->id, 403);

        $order->load([
            'items.product',
            'items.addons',
            'payments',
            'address',
            'promotion',
        ]);

        return view('kiosk.receipt', [
            'order' => $order,
        ]);
    }

    /**
     * @param  Collection<int, CartItem>  $cartItems
     * @return array<string, float>
     */
    private function calculateSummary($cartItems, ?Promotion $promotion = null): array
    {
        $subtotal = $cartItems->sum(function (CartItem $item) {
            $base = (float) $item->product->base_price;
            $sizeAdjustment = (float) ($item->size?->price_adjustment ?? 0);
            $addonsTotal = $item->addons->sum(fn ($addon) => (float) $addon->price);

            return ($base + $sizeAdjustment + $addonsTotal) * $item->quantity;
        });

        $baseTotal = $subtotal;
        $promotionDiscount = min((float) ($promotion?->discount_value ?? 0), $baseTotal);

        return [
            'subtotal' => $subtotal,
            'promotion_discount' => $promotionDiscount,
            'total' => max(0, $baseTotal - $promotionDiscount),
        ];
    }

    private function resolvePromotion(?string $promoCode): ?Promotion
    {
        $code = trim((string) $promoCode);

        if ($code === '') {
            return null;
        }

        return Promotion::query()->byCode($code)->first();
    }
}
