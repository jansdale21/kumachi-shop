<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class KioskCartController extends Controller
{
    public function index(): View
    {
        $cart = Cart::query()
            ->firstOrCreate(['user_id' => request()->user()?->id]);

        $cart->load([
            'items.product',
            'items.size',
            'items.addons',
        ]);

        return view('kiosk.cart', [
            'cart' => $cart,
            'summary' => $this->buildSummary($cart->items),
        ]);
    }

    public function store(StoreCartItemRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        if (strtolower((string) $product->availability) !== 'available') {
            return redirect()
                ->route('kiosk.products.show', $product)
                ->with('status', 'This product is currently unavailable.');
        }

        $selectedAddonIds = collect($validated['addon_ids'] ?? [])
            ->map(static fn ($id) => (int) $id)
            ->values()
            ->all();

        $selectedAddons = $product->addons()
            ->whereIn('addons.id', $selectedAddonIds)
            ->with('inventory')
            ->get();

        $allowedAddonIds = $selectedAddons
            ->filter(function ($addon) use ($validated) {
                if ($addon->inventory_id === null) {
                    return true;
                }

                $inventory = $addon->inventory;
                if (! $inventory) {
                    return false;
                }

                $usagePerAddon = (float) ($addon->inventory_usage_qty ?? 1);
                $requiredBase = $usagePerAddon * (int) $validated['quantity'];

                return $inventory->quantityInBaseUnits() >= $requiredBase;
            })
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        if (count($selectedAddonIds) !== count($allowedAddonIds)) {
            return redirect()
                ->route('kiosk.products.show', $product)
                ->withInput()
                ->with('error', 'One or more selected add-ons are currently unavailable.');
        }

        $cart = Cart::query()->firstOrCreate([
            'user_id' => request()->user()?->id,
        ]);

        $existingItems = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_size_id', $validated['product_size_id'])
            ->where('sugar_level', $validated['sugar_level'])
            ->where('ice_level', $validated['ice_level'])
            ->with('addons:id')
            ->get();

        $matchingItem = $existingItems->first(function (CartItem $item) use ($allowedAddonIds) {
            $itemAddonIds = $item->addons
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            return $itemAddonIds === $allowedAddonIds;
        });

        if ($matchingItem instanceof CartItem) {
            $newQuantity = min(20, $matchingItem->quantity + (int) $validated['quantity']);

            $matchingItem->update(['quantity' => $newQuantity]);
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $product->id,
                'product_size_id' => $validated['product_size_id'],
                'sugar_level' => $validated['sugar_level'],
                'ice_level' => $validated['ice_level'],
                'quantity' => $validated['quantity'],
            ]);

            $cartItem->addons()->sync($allowedAddonIds);
        }

        return redirect()
            ->route('kiosk.cart.index')
            ->with('status', 'Item added to cart.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $userCartId = Cart::query()
            ->where('user_id', request()->user()?->id)
            ->value('id');

        if ($userCartId !== $cartItem->cart_id) {
            abort(403);
        }

        $requestedQuantity = (int) $request->validated('quantity');

        $availability = (string) $cartItem->product()->value('availability');
        if (strtolower(trim($availability)) !== 'available') {
            return redirect()
                ->route('kiosk.cart.index')
                ->with('error', 'This product is currently unavailable.');
        }

        $cartItem->update(['quantity' => $requestedQuantity]);

        return redirect()
            ->route('kiosk.cart.index')
            ->with('status', 'Cart quantity updated.');
    }

    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $userCartId = Cart::query()
            ->where('user_id', request()->user()?->id)
            ->value('id');

        if ($userCartId !== $cartItem->cart_id) {
            abort(403);
        }

        $cartItem->delete();

        return redirect()
            ->route('kiosk.cart.index')
            ->with('status', 'Item removed from cart.');
    }

    public function clear(): RedirectResponse
    {
        $cart = Cart::query()
            ->where('user_id', request()->user()?->id)
            ->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return redirect()
            ->route('kiosk.menu')
            ->with('status', 'Cart cleared.');
    }

    /**
     * @param  Collection<int, CartItem>  $items
     * @return array<string, float>
     */
    private function buildSummary(Collection $items): array
    {
        $subtotal = $items->sum(function (CartItem $item) {
            $base = (float) $item->product->base_price;
            $sizeAdjustment = (float) ($item->size?->price_adjustment ?? 0);
            $addonsTotal = $item->addons->sum(fn ($addon) => (float) $addon->price);

            return ($base + $sizeAdjustment + $addonsTotal) * $item->quantity;
        });

        return [
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ];
    }
}
