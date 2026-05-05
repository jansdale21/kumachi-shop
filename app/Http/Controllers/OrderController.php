<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyTransaction;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const PESOS_PER_POINT_EARNED = 20;

    private const CASH_PICKUP_WARNING_THRESHOLD = 2;

    private const CASH_PICKUP_RESTRICTION_THRESHOLD = 3;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $status = strtolower(trim((string) $request->query('status', '')));
        $search = trim((string) $request->query('q', ''));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));

        $fromDate = null;
        $toDate = null;

        try {
            $fromDate = $from !== '' ? CarbonImmutable::parse($from)->startOfDay() : null;
        } catch (\Throwable) {
            $fromDate = null;
        }

        try {
            $toDate = $to !== '' ? CarbonImmutable::parse($to)->endOfDay() : null;
        } catch (\Throwable) {
            $toDate = null;
        }

        if ($fromDate && $toDate && $fromDate->greaterThan($toDate)) {
            [$fromDate, $toDate] = [$toDate->startOfDay(), $fromDate->endOfDay()];
        }

        $orders = Order::query()
            ->with(['user', 'items', 'address'])
            ->when($status !== '' && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                $query->where('created_at', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                $query->where('created_at', '<=', $toDate);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        $statuses = [
            'pending' => 'Pending',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => $statuses,
            'currentStatus' => $status,
            'search' => $search,
            'from' => $fromDate?->toDateString() ?? $from,
            'to' => $toDate?->toDateString() ?? $to,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): View
    {
        $order->load(['user', 'items.product', 'items.addons', 'address', 'promotion', 'loyaltyTransactions']);

        $statuses = [
            'pending' => 'Pending',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ];

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,preparing,ready,completed,cancelled,refunded',
        ]);

        $previousStatus = (string) $order->status;
        $targetStatus = (string) $validated['status'];

        $order->update(['status' => $targetStatus]);

        if ($targetStatus === 'completed' && strtolower((string) $order->payment_status) === 'pending') {
            $order->update(['payment_status' => 'paid']);
            $order->payments()
                ->where('status', 'pending')
                ->update(['status' => 'paid']);
        }

        if ($targetStatus === 'cancelled') {
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

                $targetStatus = 'refunded';
            } else {
                $order->update(['payment_status' => 'cancelled']);
                $order->payments()
                    ->where('status', 'pending')
                    ->update(['status' => 'cancelled']);
                $this->applyCashPickupPolicyForFailedPickup($order, $previousStatus);
            }
        }

        if ($targetStatus === 'refunded') {
            $order->update(['payment_status' => 'refunded']);
            $order->payments()
                ->where('payment_method', 'card')
                ->whereIn('status', ['paid', 'pending'])
                ->update(['status' => 'refunded']);
        }

        if (
            $order->user_id
            && $previousStatus !== 'completed'
            && $validated['status'] === 'completed'
            && ! LoyaltyTransaction::query()
                ->where('order_id', $order->id)
                ->where('type', 'earned')
                ->exists()
        ) {
            $pointsEarned = max(0, (int) floor(((float) $order->total_amount) / self::PESOS_PER_POINT_EARNED));

            if ($pointsEarned > 0) {
                LoyaltyTransaction::query()->create([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'type' => 'earned',
                    'points' => $pointsEarned,
                ]);

                User::query()->whereKey($order->user_id)->increment('loyalty_points', $pointsEarned);
            }
        }

        if ($order->user_id) {
            $publicOrderId = sprintf('KM%06d', (int) $order->id);
            Notification::query()->create([
                'user_id' => $order->user_id,
                'message' => $this->buildUserOrderStatusMessage(
                    $publicOrderId,
                    $targetStatus,
                    (string) $order->order_type
                ),
                'link' => route('orders.show', $order),
            ]);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('status', "Order #{$order->id} status updated to {$targetStatus}");
    }

    private function buildUserOrderStatusMessage(string $publicOrderId, string $status, string $orderType): string
    {
        $normalizedOrderType = strtolower(trim($orderType));
        $readyLabel = $normalizedOrderType === 'delivery' ? 'ready for delivery' : 'ready for pickup';

        return match (strtolower(trim($status))) {
            'pending' => "Your order {$publicOrderId} is now pending confirmation.",
            'preparing' => "Your order {$publicOrderId} is now being prepared.",
            'ready' => "Your order {$publicOrderId} is now {$readyLabel}.",
            'completed' => "Your order {$publicOrderId} has been completed.",
            'cancelled' => "Your order {$publicOrderId} has been cancelled.",
            'refunded' => "Your order {$publicOrderId} has been cancelled and refunded.",
            default => "Your order {$publicOrderId} status was updated.",
        };
    }

    private function applyCashPickupPolicyForFailedPickup(Order $order, string $previousStatus): void
    {
        if (! $order->user_id || strtolower((string) $order->order_type) !== 'pickup') {
            return;
        }

        if (strtolower((string) $previousStatus) !== 'ready') {
            return;
        }

        $hadCashPayment = $order->payments()
            ->where('payment_method', 'cash')
            ->exists();

        if (! $hadCashPayment) {
            return;
        }

        $user = User::query()->find($order->user_id);
        if (! $user) {
            return;
        }

        $failedCount = ((int) $user->failed_pickup_count) + 1;
        $restricted = $failedCount >= self::CASH_PICKUP_RESTRICTION_THRESHOLD;

        $user->update([
            'failed_pickup_count' => $failedCount,
            'cash_on_pickup_restricted' => $restricted,
        ]);

        if ($failedCount >= self::CASH_PICKUP_WARNING_THRESHOLD && ! $restricted) {
            Notification::query()->create([
                'user_id' => $user->id,
                'message' => 'Warning: another failed pickup will restrict cash on pickup for future orders.',
                'link' => route('orders', ['status' => 'all']),
            ]);
        }

        if ($restricted) {
            Notification::query()->create([
                'user_id' => $user->id,
                'message' => 'Cash on pickup is now restricted due to repeated failed pickups. Please use card payment.',
                'link' => route('checkout.index'),
            ]);
        }
    }
}
