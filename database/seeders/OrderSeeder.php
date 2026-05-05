<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Order::query()->exists()) {
            return;
        }

        $user = User::query()->where('email', 'test@example.com')->first();
        if (! $user) {
            return;
        }

        $products = Category::query()
            ->where('name', 'Coffee')
            ->first()
            ?->products()
            ->orderBy('name')
            ->limit(3)
            ->get();

        if (! $products || $products->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'preparing', 'ready', 'completed', 'cancelled'];

        for ($i = 1; $i <= 5; $i++) {
            $product = $products[($i - 1) % $products->count()];
            $unitPrice = (float) $product->base_price;

            $order = Order::query()->create([
                'user_id' => $user->id,
                'address_id' => null,
                'promotion_id' => null,
                'order_type' => $i % 2 === 0 ? 'pickup' : 'delivery',
                'order_source' => $i % 2 === 0 ? 'kiosk' : 'online',
                'status' => $statuses[$i - 1],
                'payment_status' => $statuses[$i - 1] === 'cancelled' ? 'failed' : 'paid',
                'total_amount' => $unitPrice * $i,
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $i,
                'unit_price' => $unitPrice,
            ]);
        }
    }
}
