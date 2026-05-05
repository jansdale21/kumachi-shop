<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class KioskMenuController extends Controller
{
    public function menu(): View
    {
        $products = Product::query()
            ->with('category')
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        $cart = Cart::query()
            ->firstOrCreate(['user_id' => request()->user()?->id]);

        $cart->load(['items.product', 'items.size', 'items.addons']);

        return view('kiosk.menu', [
            'products' => $products,
            'categories' => $categories,
            'cart' => $cart,
            'summary' => $this->buildSummary($cart->items),
        ]);
    }

    public function product(Product $product): View
    {
        $product->load([
            'category',
            'sizes' => fn ($query) => $query->orderBy('price_adjustment'),
            'addons' => fn ($query) => $query
                ->with('inventory:id,quantity,unit,base_unit,units_per_stock_unit')
                ->orderBy('name'),
        ]);

        return view('kiosk.product-detail', [
            'product' => $product,
        ]);
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
