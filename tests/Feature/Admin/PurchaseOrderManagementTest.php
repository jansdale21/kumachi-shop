<?php

namespace Tests\Feature\Admin;

use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderManagementTest extends TestCase
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

    public function test_admin_can_create_and_receive_purchase_order_updates_inventory(): void
    {
        $admin = $this->createAdmin();
        $supplier = Supplier::query()->create([
            'supplier_name' => 'Test Supplier',
            'is_active' => true,
        ]);
        $inventory = Inventory::query()->create([
            'item_name' => 'Coffee Beans',
            'quantity' => 10,
            'reorder_level' => 5,
        ]);
        $supplier->inventoryItems()->attach($inventory->id);

        $response = $this->actingAs($admin)->post(route('admin.purchase-orders.store'), [
            'supplier_id' => $supplier->id,
            'items' => [
                ['inventory_id' => $inventory->id, 'quantity' => 5, 'unit_cost' => 50],
            ],
        ]);

        $purchaseOrder = PurchaseOrder::query()->first();
        $this->assertNotNull($purchaseOrder);
        $this->assertSame(250.0, (float) $purchaseOrder->total_amount);
        $response->assertRedirect(route('admin.purchase-orders.show', $purchaseOrder));

        $orderedResponse = $this->actingAs($admin)->put(route('admin.purchase-orders.update', $purchaseOrder), [
            'status' => 'ordered',
        ]);
        $orderedResponse->assertRedirect(route('admin.purchase-orders.show', $purchaseOrder));
        $inventory->refresh();
        $this->assertSame(10.0, (float) $inventory->quantity);

        $receiveResponse = $this->actingAs($admin)->put(route('admin.purchase-orders.update', $purchaseOrder), [
            'status' => 'received',
        ]);

        $receiveResponse->assertRedirect(route('admin.purchase-orders.show', $purchaseOrder));
        $inventory->refresh();
        $this->assertSame(15.0, (float) $inventory->quantity);
    }
}
