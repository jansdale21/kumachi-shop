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

class CustomerCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_customized_product_to_cart(): void
    {
        $role = Role::query()->create(['role_name' => 'user']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $category = Category::query()->create(['name' => 'Coffee']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'base_price' => 120,
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

        $response = $this->actingAs($user)->post(route('cart.store', $product), [
            'product_size_id' => $size->id,
            'sugar_level' => 50,
            'ice_level' => 50,
            'quantity' => 2,
            'addon_ids' => [$addon->id],
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseCount('cart_item_addons', 1);
    }

    public function test_guest_is_redirected_when_adding_to_cart(): void
    {
        $category = Category::query()->create(['name' => 'Coffee']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'base_price' => 120,
            'availability' => 'available',
        ]);
        $size = ProductSize::query()->create([
            'product_id' => $product->id,
            'size_name' => 'Small',
            'price_adjustment' => 0,
        ]);

        $response = $this->post(route('cart.store', $product), [
            'product_size_id' => $size->id,
            'sugar_level' => 50,
            'ice_level' => 50,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_add_to_cart_when_product_is_unavailable(): void
    {
        $role = Role::query()->create(['role_name' => 'user']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $category = Category::query()->create(['name' => 'Coffee']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'base_price' => 120,
            'availability' => 'unavailable',
        ]);
        $size = ProductSize::query()->create([
            'product_id' => $product->id,
            'size_name' => 'Small',
            'price_adjustment' => 0,
        ]);

        $this->actingAs($user)->post(route('cart.store', $product), [
            'product_size_id' => $size->id,
            'sugar_level' => 50,
            'ice_level' => 50,
            'quantity' => 1,
        ])->assertRedirect(route('products.show', $product));
    }

    public function test_cart_quantity_must_not_exceed_twenty(): void
    {
        $role = Role::query()->create(['role_name' => 'user']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $category = Category::query()->create(['name' => 'Coffee']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Latte',
            'base_price' => 120,
            'availability' => 'available',
        ]);
        $size = ProductSize::query()->create([
            'product_id' => $product->id,
            'size_name' => 'Small',
            'price_adjustment' => 0,
        ]);

        $this->actingAs($user)->post(route('cart.store', $product), [
            'product_size_id' => $size->id,
            'sugar_level' => 50,
            'ice_level' => 50,
            'quantity' => 1,
        ])->assertRedirect(route('cart.index'));

        $cartItemId = Cart::query()->where('user_id', $user->id)->first()->items()->value('id');

        $response = $this->actingAs($user)->patch(route('cart.items.update', $cartItemId), [
            'quantity' => 21,
        ]);

        $response->assertSessionHasErrors('quantity');

        $this->assertSame(
            1,
            (int) Cart::query()
                ->where('user_id', $user->id)
                ->first()
                ->items()
                ->whereKey($cartItemId)
                ->value('quantity')
        );
    }
}
