<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private function nextOpenScheduledFor(): string
    {
        $now = now(config('app.timezone'));
        $maxDays = (int) config('shop.schedule_max_days', 7);

        for ($i = 0; $i <= $maxDays; $i++) {
            $date = $now->copy()->addDays($i);
            $weekday = (int) $date->dayOfWeekIso;
            $hours = (array) config("shop.hours.{$weekday}", []);
            $open = (string) ($hours['open'] ?? '');
            $close = (string) ($hours['close'] ?? '');

            if ($open === '' || $close === '') {
                continue;
            }

            $openAt = \Illuminate\Support\Carbon::parse($date->format('Y-m-d')." {$open}", config('app.timezone'));
            $closeAt = \Illuminate\Support\Carbon::parse($date->format('Y-m-d')." {$close}", config('app.timezone'));

            // pick the next 1-hour slot inside open hours
            $candidate = $now->copy()->addHour();
            if ($candidate->lt($openAt)) {
                $candidate = $openAt->copy()->addMinutes(30);
            }

            if ($candidate->betweenIncluded($openAt, $closeAt)) {
                return $candidate->format('Y-m-d H:i:s');
            }
        }

        // fallback: 1 hour from now (may fail if config is mis-set)
        return $now->copy()->addHour()->format('Y-m-d H:i:s');
    }

    private function createUser(): User
    {
        $role = Role::query()->create([
            'role_name' => 'user',
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
        ]);
    }

    public function test_user_can_checkout_and_place_order_with_mock_payment(): void
    {
        $user = $this->createUser();
        $category = Category::query()->create(['name' => 'Coffee']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'base_price' => 100,
            'availability' => 'available',
        ]);
        $size = ProductSize::query()->create([
            'product_id' => $product->id,
            'size_name' => 'Medium',
            'price_adjustment' => 0.50,
        ]);
        $addon = Addon::query()->create([
            'name' => 'Extra Shot',
            'price' => 0.75,
        ]);
        $product->addons()->attach($addon->id);

        $cart = Cart::query()->create(['user_id' => $user->id]);
        $cartItem = $cart->items()->create([
            'product_id' => $product->id,
            'product_size_id' => $size->id,
            'sugar_level' => 50,
            'ice_level' => 50,
            'quantity' => 2,
        ]);
        $cartItem->addons()->attach($addon->id);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'order_type' => 'pickup',
            'scheduled_for' => $this->nextOpenScheduledFor(),
            'payment_method' => 'card',
            'card_holder_name' => 'Test User',
            'card_number' => '4111 1111 1111 1111',
            'card_expiry' => '12/2030',
            'card_cvv' => '123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('order_item_addons', 1);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_fails_when_product_is_unavailable(): void
    {
        $user = $this->createUser();
        $category = Category::query()->create(['name' => 'Coffee']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'base_price' => 100,
            'availability' => 'unavailable',
        ]);
        $size = ProductSize::query()->create([
            'product_id' => $product->id,
            'size_name' => 'Medium',
            'price_adjustment' => 0.50,
        ]);

        $cart = Cart::query()->create(['user_id' => $user->id]);
        $cart->items()->create([
            'product_id' => $product->id,
            'product_size_id' => $size->id,
            'sugar_level' => 50,
            'ice_level' => 50,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'order_type' => 'pickup',
            'scheduled_for' => $this->nextOpenScheduledFor(),
            'payment_method' => 'card',
            'card_holder_name' => 'Test User',
            'card_number' => '4111 1111 1111 1111',
            'card_expiry' => '12/2030',
            'card_cvv' => '123',
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_history_page_fetches_orders_from_database(): void
    {
        $user = $this->createUser();

        $order = $user->orders()->create([
            'address_id' => null,
            'promotion_id' => null,
            'order_type' => 'pickup',
            'order_source' => 'online',
            'status' => 'pending',
            'payment_status' => 'pending',
            'total_amount' => 250.00,
        ]);

        $response = $this->actingAs($user)->get(route('orders'));

        $response->assertOk();
        $response->assertSee('KM'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT));
    }

    public function test_user_can_reorder_previous_order(): void
    {
        $user = $this->createUser();
        $category = Category::query()->create(['name' => 'Coffee']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Americano',
            'base_price' => 110,
            'availability' => 'available',
        ]);
        ProductSize::query()->create([
            'product_id' => $product->id,
            'size_name' => 'Small',
            'price_adjustment' => 0.00,
        ]);

        $order = $user->orders()->create([
            'address_id' => null,
            'promotion_id' => null,
            'order_type' => 'pickup',
            'order_source' => 'online',
            'status' => 'completed',
            'payment_status' => 'paid',
            'total_amount' => 110.00,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 110.00,
        ]);

        $response = $this->actingAs($user)->post(route('orders.reorder', $order));

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseCount('cart_items', 1);
    }
}
