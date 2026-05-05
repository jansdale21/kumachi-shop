<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addons = [
            ['name' => 'Extra Shot', 'price' => 15, 'inventory_item' => 'Espresso Shot', 'inventory_usage_qty' => 1],
            ['name' => 'Vanilla Syrup', 'price' => 10, 'inventory_item' => 'Vanilla Syrup', 'inventory_usage_qty' => 1],
            ['name' => 'Caramel Syrup', 'price' => 10, 'inventory_item' => 'Caramel Syrup', 'inventory_usage_qty' => 1],
            ['name' => 'Hazelnut Syrup', 'price' => 10, 'inventory_item' => 'Hazelnut Syrup', 'inventory_usage_qty' => 1],
            ['name' => 'Whipped Cream', 'price' => 10, 'inventory_item' => 'Whipped Cream', 'inventory_usage_qty' => 1],
            ['name' => 'Almond Milk', 'price' => 15, 'inventory_item' => 'Almond Milk', 'inventory_usage_qty' => 1],
            ['name' => 'Oat Milk', 'price' => 15, 'inventory_item' => 'Oat Milk', 'inventory_usage_qty' => 1],
            ['name' => 'Soy Milk', 'price' => 15, 'inventory_item' => 'Soy Milk', 'inventory_usage_qty' => 1],
        ];

        foreach ($addons as $addon) {
            $inventoryId = Inventory::query()
                ->where('item_name', $addon['inventory_item'])
                ->value('id');

            Addon::query()->updateOrCreate(
                ['name' => $addon['name']],
                [
                    'price' => $addon['price'],
                    'inventory_id' => $inventoryId,
                    'inventory_usage_qty' => $addon['inventory_usage_qty'],
                ]
            );
        }
    }
}
