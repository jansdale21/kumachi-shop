<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
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

    public function test_admin_can_create_inventory_item(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.inventories.store'), [
            'item_name' => 'Coffee Beans - Arabica',
            'quantity' => 100,
            'reorder_level' => 20,
        ]);

        $response->assertRedirect(route('admin.inventories.index'));
        $this->assertDatabaseHas('inventories', [
            'item_name' => 'Coffee Beans - Arabica',
        ]);
    }

    public function test_non_admin_cannot_access_inventory_management_page(): void
    {
        $role = Role::query()->firstOrCreate([
            'role_name' => 'user',
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.inventories.index'));

        $response->assertRedirect(route('home'));
    }
}
