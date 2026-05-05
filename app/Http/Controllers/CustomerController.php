<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function home(): View
    {
        $featuredProducts = Product::query()
            ->with('category')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        return view('customer.home', [
            'featuredProducts' => $featuredProducts,
        ]);
    }

    public function menu(): View
    {
        $products = Product::query()
            ->with('category')
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('customer.menu', [
            'products' => $products,
            'categories' => $categories,
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

        return view('customer.product-detail', [
            'product' => $product,
        ]);
    }

    public function rewards(): View
    {
        $user = request()->user();

        $pointsBalance = 0;
        $activities = collect();

        if ($user instanceof User) {
            $computedBalance = (int) LoyaltyTransaction::query()
                ->where('user_id', $user->id)
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'earned' THEN points ELSE -points END), 0) as points_balance")
                ->value('points_balance');

            $pointsBalance = max((int) $user->loyalty_points, $computedBalance);

            $activities = LoyaltyTransaction::query()
                ->where('user_id', $user->id)
                ->with('order')
                ->latest()
                ->limit(8)
                ->get();
        }

        $usedPromotionIds = $user instanceof User
            ? Order::query()
                ->where('user_id', $user->id)
                ->whereNotNull('promotion_id')
                ->pluck('promotion_id')
                ->filter()
                ->all()
            : [];

        $activePromotions = Promotion::query()
            ->whereNotNull('code')
            ->available()
            ->when($usedPromotionIds !== [], function ($query) use ($usedPromotionIds) {
                $query->whereNotIn('id', $usedPromotionIds);
            })
            ->orderBy('discount_value', 'desc')
            ->limit(6)
            ->get();

        return view('customer.rewards', [
            'pointsBalance' => $pointsBalance,
            'nextRewardThreshold' => 100,
            'activities' => $activities,
            'activePromotions' => $activePromotions,
        ]);
    }
}
