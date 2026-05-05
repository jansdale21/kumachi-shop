<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\LoyaltyTransaction;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    private const PESOS_PER_POINT_EARNED = 20;

    private const PESO_PER_POINT_REDEEMED = 1.0;

    private const CASH_PICKUP_WARNING_THRESHOLD = 2;

    private const CASH_PICKUP_RESTRICTION_THRESHOLD = 3;

    public function index(): View
    {
        $user = request()->user();

        $orders = Order::query()
            ->where('user_id', $user?->id)
            ->with(['items.product', 'payments'])
            ->latest()
            ->get();

        return view('customer.orders', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorizeOrderAccess($order);

        $order->load([
            'items.product',
            'items.addons',
            'payments',
            'address',
            'promotion',
            'loyaltyTransactions',
        ]);

        return view('customer.order-detail', [
            'order' => $order,
        ]);
    }

    public function receipt(Order $order): View
    {
        $this->authorizeOrderAccess($order);

        $order->load([
            'items.product',
            'items.addons',
            'payments',
            'address',
            'promotion',
            'loyaltyTransactions',
        ]);

        return view('customer.receipt', [
            'order' => $order,
        ]);
    }

    public function checkout(): View|RedirectResponse
    {
        $user = request()->user();

        $cart = Cart::query()
            ->firstOrCreate(['user_id' => $user?->id]);

        $cart->load(['items.product', 'items.size', 'items.addons']);

        if ($cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('status', 'Your cart is empty.');
        }

        $addresses = Address::query()
            ->where('user_id', $user?->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        $usedPromotionIds = Order::query()
            ->where('user_id', $user?->id)
            ->whereNotNull('promotion_id')
            ->pluck('promotion_id')
            ->filter()
            ->all();

        $availablePromotions = Promotion::query()
            ->whereNotNull('code')
            ->available()
            ->when($usedPromotionIds !== [], function ($query) use ($usedPromotionIds) {
                $query->whereNotIn('id', $usedPromotionIds);
            })
            ->orderBy('code')
            ->get();

        $paymentMethods = PaymentMethod::query()
            ->where('user_id', $user?->id)
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return view('customer.checkout', [
            'cart' => $cart,
            'addresses' => $addresses,
            'summary' => $this->calculateSummary($cart->items),
            'availablePoints' => $user instanceof User ? $this->availableLoyaltyPoints($user) : 0,
            'availablePromotions' => $availablePromotions,
            'paymentMethods' => $paymentMethods,
            'cashPickupFailedCount' => (int) ($user?->failed_pickup_count ?? 0),
            'cashPickupRestrictionThreshold' => self::CASH_PICKUP_RESTRICTION_THRESHOLD,
            'cashPickupWarningThreshold' => self::CASH_PICKUP_WARNING_THRESHOLD,
            'cashPickupRestricted' => (bool) ($user?->cash_on_pickup_restricted ?? false),
        ]);
    }

    public function placeOrder(PlaceOrderRequest $request): RedirectResponse
    {
        $cart = Cart::query()
            ->where('user_id', request()->user()?->id)
            ->with(['items.product', 'items.size', 'items.addons'])
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('status', 'Your cart is empty.');
        }

        $validated = $request->validated();
        $promotion = $this->resolvePromotion($validated['promo_code'] ?? null);
        $requestedRedeemPoints = max(0, (int) ($validated['redeem_points'] ?? 0));

        try {
            $order = DB::transaction(function () use ($validated, $cart, $promotion, $requestedRedeemPoints) {
                $user = User::query()
                    ->lockForUpdate()
                    ->find(request()->user()?->id);

                if (! $user) {
                    throw new \RuntimeException('Unable to find customer account.');
                }

                $this->ensurePromotionCanBeUsedByUser($user, $promotion);
                $this->ensureCashOnPickupAllowedForUser($user, $validated);

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
                }

                $availablePoints = $this->availableLoyaltyPoints($user);
                $redeemPoints = min($requestedRedeemPoints, $availablePoints);
                $summary = $this->calculateSummary($cart->items, $promotion, $redeemPoints, $validated['order_type'] ?? 'pickup');

                $addressId = null;

                if (($validated['order_type'] ?? 'pickup') === 'delivery') {
                    if (empty($validated['address_id']) && isset($validated['full_name'], $validated['phone'], $validated['street'], $validated['city'])) {
                        $address = Address::query()->create([
                            'user_id' => $user->id,
                            'full_name' => $validated['full_name'],
                            'phone' => $validated['phone'],
                            'street' => $validated['street'],
                            'city' => $validated['city'],
                        ]);
                        $addressId = $address->id;
                    } else {
                        $addressId = $validated['address_id'] ?? null;
                    }
                }

                $paymentStatus = ($validated['payment_method'] ?? 'cash') === 'cash' ? 'pending' : 'paid';

                $order = Order::query()->create([
                    'user_id' => $user->id,
                    'address_id' => $addressId,
                    'promotion_id' => $promotion?->id,
                    'order_type' => $validated['order_type'] ?? 'pickup',
                    'order_source' => 'online',
                    'status' => 'pending',
                    'payment_status' => $paymentStatus,
                    'total_amount' => $summary['total'],
                ]);

                $inventoryAlerts = [];
                $addonInventoryUsage = [];

                foreach ($cart->items as $cartItem) {
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

                if (
                    ($validated['payment_method'] ?? 'cash') === 'card'
                    && empty($validated['saved_payment_method_id'])
                    && ($validated['save_new_card'] ?? false)
                    && ! empty($validated['card_number'])
                    && ! empty($validated['card_expiry'])
                    && ! empty($validated['card_holder_name'])
                ) {
                    $cardNumber = preg_replace('/\D+/', '', $validated['card_number']);
                    [$expMonth, $expYear] = explode('/', $validated['card_expiry']);
                    $normalizedYear = strlen((string) $expYear) === 2 ? (int) ('20'.$expYear) : (int) $expYear;

                    $savedCard = $user->paymentMethods()->create([
                        'label' => 'Saved Card',
                        'cardholder_name' => $validated['card_holder_name'],
                        'card_brand' => 'Card',
                        'card_last4' => substr((string) $cardNumber, -4),
                        'cvv_hash' => Hash::make((string) ($validated['card_cvv'] ?? '')),
                        'exp_month' => (int) $expMonth,
                        'exp_year' => $normalizedYear,
                        'is_default' => false,
                    ]);

                    $validated['saved_payment_method_id'] = $savedCard->id;
                    $validated['saved_card_cvv'] = $validated['card_cvv'] ?? null;
                }

                if (($validated['payment_method'] ?? 'cash') === 'card' && ! empty($validated['saved_payment_method_id'])) {
                    $savedMethod = PaymentMethod::query()
                        ->where('id', (int) $validated['saved_payment_method_id'])
                        ->where('user_id', $user->id)
                        ->first();

                    if (! $savedMethod) {
                        throw new \RuntimeException('Selected saved card was not found.');
                    }

                    if (empty($savedMethod->cvv_hash) || ! Hash::check((string) ($validated['saved_card_cvv'] ?? ''), $savedMethod->cvv_hash)) {
                        throw new \RuntimeException('Saved card CVV does not match.');
                    }
                }

                Payment::query()->create([
                    'order_id' => $order->id,
                    'payment_method' => $validated['payment_method'],
                    'saved_payment_method_id' => $validated['saved_payment_method_id'] ?? null,
                    'amount' => $summary['total'],
                    'status' => $paymentStatus,
                ]);

                if ($summary['points_discount'] > 0) {
                    $pointsRedeemed = $redeemPoints;

                    LoyaltyTransaction::query()->create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'type' => 'redeemed',
                        'points' => $pointsRedeemed,
                    ]);

                    $user->decrement('loyalty_points', $pointsRedeemed);
                }

                // Cart cleanup after successful order should fully clear transient cart rows.
                $cart->items()->forceDelete();

                Notification::query()->create([
                    'user_id' => $user->id,
                    'message' => 'Your order '.sprintf('KM%06d', (int) $order->id).' was placed successfully.',
                    'link' => route('orders.show', $order),
                ]);

                $adminRoleId = Role::query()->where('role_name', 'admin')->value('id');
                if ($adminRoleId) {
                    $adminIds = User::query()->where('role_id', $adminRoleId)->pluck('id');

                    foreach ($adminIds as $adminId) {
                        Notification::query()->create([
                            'user_id' => $adminId,
                            'message' => 'New online order '.sprintf('KM%06d', (int) $order->id).' requires attention.',
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
            $message = $exception->getMessage();

            if (
                str_contains(strtolower($message), 'cvv')
                || str_contains(strtolower($message), 'promo')
                || str_contains(strtolower($message), 'promotion')
            ) {
                return redirect()
                    ->route('checkout.index')
                    ->withInput()
                    ->with('error', $message);
            }

            return redirect()
                ->route('cart.index')
                ->with('error', $message);
        }

        return redirect()
            ->route('orders.receipt', $order)
            ->with('status', 'Order placed successfully.');
    }

    public function reorder(Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($order);

        $order->load(['items.product', 'items.addons', 'items.product.sizes']);

        $cart = Cart::query()->firstOrCreate([
            'user_id' => request()->user()?->id,
        ]);

        try {
            DB::transaction(function () use ($order, $cart) {
                foreach ($order->items as $orderItem) {
                    if (! $orderItem->product) {
                        continue;
                    }

                    $sizeId = $orderItem->product->sizes()->value('id');
                    if (! $sizeId) {
                        continue;
                    }

                    $addonIds = $orderItem->product->addons()
                        ->whereIn('name', $orderItem->addons->pluck('addon_name'))
                        ->pluck('addons.id')
                        ->map(static fn ($id) => (int) $id)
                        ->sort()
                        ->values()
                        ->all();

                    $existingItems = $cart->items()
                        ->where('product_id', $orderItem->product_id)
                        ->where('product_size_id', $sizeId)
                        ->where('sugar_level', 50)
                        ->where('ice_level', 50)
                        ->with('addons:id')
                        ->get();

                    $matchingItem = $existingItems->first(function (CartItem $item) use ($addonIds) {
                        $itemAddonIds = $item->addons
                            ->pluck('id')
                            ->map(static fn ($id) => (int) $id)
                            ->sort()
                            ->values()
                            ->all();

                        return $itemAddonIds === $addonIds;
                    });

                    $requestedQuantity = (int) $orderItem->quantity;
                    if (strtolower((string) $orderItem->product->availability) !== 'available') {
                        throw new \RuntimeException("This product is currently unavailable to reorder: {$orderItem->product->name}.");
                    }

                    if ($matchingItem instanceof CartItem) {
                        $mergedQuantity = min(20, ((int) $matchingItem->quantity) + $requestedQuantity);

                        $matchingItem->update([
                            'quantity' => $mergedQuantity,
                        ]);
                    } else {
                        $newQuantity = min(20, $requestedQuantity);

                        $cartItem = $cart->items()->create([
                            'product_id' => $orderItem->product_id,
                            'product_size_id' => $sizeId,
                            'sugar_level' => 50,
                            'ice_level' => 50,
                            'quantity' => $newQuantity,
                        ]);

                        $cartItem->addons()->sync($addonIds);
                    }
                }
            });
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('cart.index')
            ->with('status', 'Items added back to your cart.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($order);

        if (! in_array((string) $order->status, ['pending', 'preparing'], true)) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'Only pending or preparing orders can be cancelled.');
        }

        DB::transaction(function () use ($order): void {
            $hasCardPayment = $order->payments()
                ->where('payment_method', 'card')
                ->whereIn('status', ['paid', 'pending'])
                ->exists();

            if ($hasCardPayment) {
                $order->update([
                    'status' => 'refunded',
                    'payment_status' => 'refunded',
                ]);

                $order->payments()
                    ->where('payment_method', 'card')
                    ->update(['status' => 'refunded']);
            } else {
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'cancelled',
                ]);

                $order->payments()
                    ->where('status', 'pending')
                    ->update(['status' => 'cancelled']);
            }
        });

        Notification::query()->create([
            'user_id' => $order->user_id,
            'message' => $order->status === 'refunded'
                ? 'Your order has been cancelled and refunded to your original payment method.'
                : 'Your order has been cancelled.',
            'link' => route('orders.show', $order),
        ]);

        $adminRoleId = Role::query()->where('role_name', 'admin')->value('id');
        if ($adminRoleId) {
            $adminIds = User::query()->where('role_id', $adminRoleId)->pluck('id');

            foreach ($adminIds as $adminId) {
                Notification::query()->create([
                    'user_id' => $adminId,
                    'message' => 'Customer cancelled order '.sprintf('KM%06d', (int) $order->id).'.',
                    'link' => route('admin.orders.show', $order),
                ]);
            }
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('status', $order->status === 'refunded'
                ? 'Order cancelled and refunded successfully.'
                : 'Order cancelled successfully.');
    }

    /**
     * @param  Collection<int, CartItem>  $cartItems
     * @return array<string, float>
     */
    private function calculateSummary($cartItems, ?Promotion $promotion = null, int $redeemPoints = 0, string $orderType = 'pickup'): array
    {
        $subtotal = $cartItems->sum(function (CartItem $item) {
            $base = (float) $item->product->base_price;
            $sizeAdjustment = (float) ($item->size?->price_adjustment ?? 0);
            $addonsTotal = $item->addons->sum(fn ($addon) => (float) $addon->price);

            return ($base + $sizeAdjustment + $addonsTotal) * $item->quantity;
        });

        $baseTotal = $subtotal;

        $promotionDiscount = min((float) ($promotion?->discount_value ?? 0), $baseTotal);
        $remainingAfterPromotion = max(0, $baseTotal - $promotionDiscount);
        $pointsDiscount = min((float) max(0, $redeemPoints) * self::PESO_PER_POINT_REDEEMED, $remainingAfterPromotion);
        $deliveryFee = $orderType === 'delivery' ? 50.0 : 0.0;

        return [
            'subtotal' => $subtotal,
            'promotion_discount' => $promotionDiscount,
            'points_discount' => $pointsDiscount,
            'delivery_fee' => $deliveryFee,
            'total' => max(0, $baseTotal - $promotionDiscount - $pointsDiscount) + $deliveryFee,
        ];
    }

    private function resolvePromotion(?string $promoCode): ?Promotion
    {
        $code = trim((string) $promoCode);

        if ($code === '') {
            return null;
        }

        return Promotion::query()
            ->available()
            ->byCode($code)
            ->first();
    }

    private function ensurePromotionCanBeUsedByUser(User $user, ?Promotion $promotion): void
    {
        if (! $promotion) {
            return;
        }

        $alreadyUsed = Order::query()
            ->where('user_id', $user->id)
            ->where('promotion_id', $promotion->id)
            ->exists();

        if ($alreadyUsed) {
            throw new \RuntimeException('This promo code has already been used and can only be used once.');
        }
    }

    private function availableLoyaltyPoints(User $user): int
    {
        $pointsBalance = (int) LoyaltyTransaction::query()
            ->where('user_id', $user->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'earned' THEN points ELSE -points END), 0) as points_balance")
            ->value('points_balance');

        return max((int) $user->loyalty_points, $pointsBalance);
    }

    private function calculateEarnedPoints(float $orderTotal): int
    {
        return max(0, (int) floor($orderTotal / self::PESOS_PER_POINT_EARNED));
    }

    private function ensureCashOnPickupAllowedForUser(User $user, array $validated): void
    {
        $orderType = (string) ($validated['order_type'] ?? 'pickup');
        $paymentMethod = (string) ($validated['payment_method'] ?? 'cash');

        if (
            $orderType === 'pickup'
            && $paymentMethod === 'cash'
            && (bool) $user->cash_on_pickup_restricted
        ) {
            throw new \RuntimeException('Cash on pickup is restricted for your account. Please use card payment.');
        }
    }

    private function authorizeOrderAccess(Order $order): void
    {
        abort_if($order->user_id !== request()->user()?->id, 403);
    }
}
