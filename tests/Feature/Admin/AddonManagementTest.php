<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddonManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_addon_and_assign_products(): void
    {
        $adminRole = Role::query()->create(['role_name' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $category = Category::query()->create(['name' => 'Coffee']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Latte',
            'base_price' => 120,
            'availability' => 'available',
        ]);
        $inventory = Inventory::query()->create([
            'item_name' => 'Vanilla Syrup Stock',
            'quantity' => 100,
            'reorder_level' => 10,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.addons.store'), [
            'name' => 'Vanilla Syrup',
            'price' => 0.50,
            'inventory_id' => $inventory->id,
            'inventory_usage_qty' => 1,
            'product_ids' => [$product->id],
        ]);

        $response->assertRedirect(route('admin.addons.index'));
        $this->assertDatabaseHas('addons', ['name' => 'Vanilla Syrup']);
        $this->assertDatabaseHas('product_addons', ['product_id' => $product->id]);
    }

    public function test_non_admin_cannot_access_addons_page(): void
    {
        $userRole = Role::query()->create(['role_name' => 'user']);
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $response = $this->actingAs($user)->get(route('admin.addons.index'));

        $response->assertRedirect(route('home'));
    }
}
