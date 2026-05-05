<?php

namespace Tests\Feature\Admin;

use App\Models\Inventory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        $role = Role::query()->firstOrCreate([
            'role_name' => 'admin',
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
        ]);
    }

    public function test_admin_can_create_supplier_with_inventory_items(): void
    {
        $admin = $this->createAdmin();
        $inventory = Inventory::query()->create([
            'item_name' => 'Coffee Beans',
            'quantity' => 100,
            'reorder_level' => 20,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.suppliers.store'), [
            'supplier_name' => 'Premium Coffee Imports',
            'contact_person' => 'John Smith',
            'email' => 'john@premiumcoffee.com',
            'phone' => '+1 555-0201',
            'address' => '123 Coffee Lane, Seattle',
            'is_active' => '1',
            'inventory_ids' => [$inventory->id],
        ]);

        $response->assertRedirect(route('admin.suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'supplier_name' => 'Premium Coffee Imports',
        ]);
        $this->assertDatabaseHas('supplier_items', [
            'inventory_id' => $inventory->id,
        ]);
    }

    public function test_non_admin_cannot_access_supplier_management_page(): void
    {
        $role = Role::query()->firstOrCreate([
            'role_name' => 'user',
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.suppliers.index'));

        $response->assertRedirect(route('home'));
    }
}
