<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $today = CarbonImmutable::today();
        $start = $today->subDays(6)->startOfDay();
        $end = $today->endOfDay();

        $todaySales = (float) Order::query()
            ->whereDate('created_at', $today)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $todayOrders = (int) Order::query()
            ->whereDate('created_at', $today)
            ->count();

        $customerRoleId = Role::query()
            ->where('role_name', 'user')
            ->value('id');

        $customersCount = $customerRoleId
            ? (int) User::query()->where('role_id', $customerRoleId)->count()
            : 0;

        $lowStockItemsCount = (int) Inventory::query()
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->where('quantity', '>', 0)
            ->count();

        $salesByDay = Order::query()
            ->selectRaw('DATE(created_at) as day, SUM(total_amount) as total')
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day')
            ->map(fn ($value) => (float) $value)
            ->all();

        $weekly = collect(range(0, 6))
            ->map(function (int $offset) use ($start, $salesByDay) {
                $day = $start->addDays($offset);
                $key = $day->format('Y-m-d');

                return [
                    'label' => $day->format('D'),
                    'date' => $key,
                    'total' => (float) ($salesByDay[$key] ?? 0),
                ];
            })
            ->values();

        $maxWeeklyTotal = max(1, (float) $weekly->max('total'));

        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.payment_status', '=', 'paid')
            ->selectRaw('products.name as name, SUM(order_items.quantity) as sold, SUM(order_items.quantity * order_items.unit_price) as revenue')
            ->groupBy('products.name')
            ->orderByDesc('sold')
            ->limit(4)
            ->get()
            ->map(fn ($row) => [
                'name' => (string) $row->name,
                'sold' => (int) $row->sold,
                'revenue' => (float) $row->revenue,
            ]);

        $recentOrders = Order::query()
            ->with(['user'])
            ->withCount('items')
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard', [
            'todaySales' => $todaySales,
            'todayOrders' => $todayOrders,
            'customersCount' => $customersCount,
            'lowStockItemsCount' => $lowStockItemsCount,
            'weekly' => $weekly,
            'maxWeeklyTotal' => $maxWeeklyTotal,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
        ]);
    }
}
