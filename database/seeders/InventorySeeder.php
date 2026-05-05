<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove demo bakery items that are not part of the menu/add-ons.
        Inventory::query()
            ->whereIn('item_name', ['Croissants', 'Muffins', 'Cookies'])
            ->delete();

        $inventoryItems = [
            ['item_name' => 'Coffee Beans - Arabica', 'unit' => 'g', 'quantity' => 120, 'reorder_level' => 30],
            ['item_name' => 'Coffee Beans - Robusta', 'unit' => 'g', 'quantity' => 90, 'reorder_level' => 25],
            ['item_name' => 'Milk - Whole', 'unit' => 'ml', 'quantity' => 60, 'reorder_level' => 20],
            ['item_name' => 'Milk - Skim', 'unit' => 'ml', 'quantity' => 40, 'reorder_level' => 15],
            ['item_name' => 'Almond Milk', 'unit' => 'ml', 'quantity' => 30, 'reorder_level' => 10],
            ['item_name' => 'Oat Milk', 'unit' => 'ml', 'quantity' => 30, 'reorder_level' => 10],
            ['item_name' => 'Soy Milk', 'unit' => 'ml', 'quantity' => 30, 'reorder_level' => 10],
            ['item_name' => 'Cream', 'unit' => 'ml', 'quantity' => 35, 'reorder_level' => 12],
            ['item_name' => 'Whipped Cream', 'unit' => 'ml', 'quantity' => 24, 'reorder_level' => 8],
            ['item_name' => 'Sugar', 'unit' => 'g', 'quantity' => 75, 'reorder_level' => 20],
            ['item_name' => 'Honey', 'unit' => 'ml', 'quantity' => 20, 'reorder_level' => 10],
            ['item_name' => 'Vanilla Syrup', 'unit' => 'ml', 'quantity' => 25, 'reorder_level' => 10],
            ['item_name' => 'Caramel Syrup', 'unit' => 'ml', 'quantity' => 18, 'reorder_level' => 8],
            ['item_name' => 'Hazelnut Syrup', 'unit' => 'ml', 'quantity' => 18, 'reorder_level' => 8],
            ['item_name' => 'Espresso Shot', 'unit' => 'ml', 'quantity' => 40, 'reorder_level' => 12],
        ];

        foreach ($inventoryItems as $item) {
            Inventory::query()->updateOrCreate(
                ['item_name' => $item['item_name']],
                [
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'reorder_level' => $item['reorder_level'],
                ]
            );
        }
    }
}
