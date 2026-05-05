<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $report = $this->buildReportData($request);

        return view('admin.reports.index', [
            ...$report,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $report = $this->buildReportData($request);

        $filename = 'kumachi-reports-'.$report['from']->toDateString().'_to_'.$report['to']->toDateString().'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($report): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Kumachi Reports Export']);
            fputcsv($out, ['From', $report['from']->toDateString()]);
            fputcsv($out, ['To', $report['to']->toDateString()]);
            fputcsv($out, []);

            fputcsv($out, ['Summary']);
            fputcsv($out, ['Total Sales', number_format((float) $report['totalSales'], 2, '.', '')]);
            fputcsv($out, ['Orders (Excluding Cancelled)', (int) $report['orderCount']]);
            fputcsv($out, ['Average Order Value', number_format((float) $report['avgOrderValue'], 2, '.', '')]);
            fputcsv($out, ['Completed Orders', (int) $report['completedOrders']]);
            fputcsv($out, ['Cancelled Orders', (int) $report['cancelledOrders']]);
            fputcsv($out, ['Completion Rate %', number_format((float) $report['completionRate'], 1, '.', '')]);
            fputcsv($out, ['Cancelled Share %', number_format((float) $report['cancellationRate'], 1, '.', '')]);
            fputcsv($out, []);

            fputcsv($out, ['Orders by Status']);
            fputcsv($out, ['Status', 'Count']);
            foreach ($report['ordersByStatus'] as $status => $count) {
                fputcsv($out, [ucfirst((string) $status), (int) $count]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Top Products by Revenue']);
            fputcsv($out, ['Product', 'Qty Sold', 'Revenue']);
            foreach ($report['topProducts'] as $product) {
                fputcsv($out, [(string) $product->name, (int) $product->qty, number_format((float) $product->revenue, 2, '.', '')]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Daily Trend']);
            fputcsv($out, ['Date', 'Orders', 'Sales']);
            foreach ($report['dailySales'] as $day) {
                fputcsv($out, [(string) $day->day, (int) $day->orders, number_format((float) $day->sales, 2, '.', '')]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportData(Request $request): array
    {
        $from = CarbonImmutable::parse((string) $request->query('from', now()->subDays(29)->toDateString()))->startOfDay();
        $to = CarbonImmutable::parse((string) $request->query('to', now()->toDateString()))->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        $base = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled');

        $totalSales = (float) (clone $base)->sum('total_amount');
        $orderCount = (int) (clone $base)->count();
        $avgOrderValue = $orderCount > 0 ? $totalSales / $orderCount : 0.0;
        $completedOrders = (int) (clone $base)->where('status', 'completed')->count();
        $allOrdersInRange = (int) Order::query()->whereBetween('created_at', [$from, $to])->count();
        $cancelledOrders = max(0, $allOrdersInRange - $orderCount);
        $cancellationRate = $allOrdersInRange > 0 ? ($cancelledOrders / $allOrdersInRange) * 100 : 0;
        $completionRate = $orderCount > 0 ? ($completedOrders / $orderCount) * 100 : 0;

        $ordersByStatus = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('products.name, SUM(order_items.quantity) as qty, SUM(order_items.quantity * order_items.unit_price) as revenue')
            ->groupBy('products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $dailySales = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(total_amount) as sales')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $salesBySource = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('order_source, COUNT(*) as orders, SUM(total_amount) as sales')
            ->groupBy('order_source')
            ->orderByDesc('sales')
            ->get();

        $paymentBreakdown = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('payments.payment_method, COUNT(*) as tx_count, SUM(payments.amount) as total_amount')
            ->groupBy('payments.payment_method')
            ->orderByDesc('total_amount')
            ->get();

        $topCustomers = Order::query()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('users.name as customer_name, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as total_spent')
            ->groupBy('users.name')
            ->orderByDesc('total_spent')
            ->limit(8)
            ->get();

        $hourlyOrders = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->selectRaw('HOUR(created_at) as hour_slot, COUNT(*) as total')
            ->groupBy('hour_slot')
            ->orderBy('hour_slot')
            ->get();

        $bestSalesDay = $dailySales->sortByDesc('sales')->first();

        return compact(
            'from',
            'to',
            'totalSales',
            'orderCount',
            'avgOrderValue',
            'completedOrders',
            'cancelledOrders',
            'completionRate',
            'cancellationRate',
            'allOrdersInRange',
            'ordersByStatus',
            'topProducts',
            'dailySales',
            'salesBySource',
            'paymentBreakdown',
            'topCustomers',
            'hourlyOrders',
            'bestSalesDay'
        );
    }
}
