<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'supplier_name' => 'Premium Coffee Imports',
                'contact_person' => 'Kaiji Yusi',
                'email' => 'yusikaiji@premiumcoffee.com',
                'phone' => '0921234567',
                'address' => 'Tokyo, Japan',
                'is_active' => true,
                'supplies' => [
                    'Coffee Beans - Arabica',
                    'Coffee Beans - Robusta',
                    'Espresso Shot',
                ],
            ],
            [
                'supplier_name' => 'Fresh Dairy Co.',
                'contact_person' => 'Miguel Mendoza',
                'email' => 'jm@freshdairy.com',
                'phone' => '097010672828',
                'address' => 'Houston, Texas',
                'is_active' => true,
                'supplies' => [
                    'Milk - Whole',
                    'Milk - Skim',
                    'Almond Milk',
                    'Oat Milk',
                    'Soy Milk',
                    'Cream',
                    'Whipped Cream',
                ],
            ],
            [
                'supplier_name' => 'Sweetness Suppliers',
                'contact_person' => 'Cha Mu Hee',
                'email' => 'chamuhee@sweetness.com',
                'phone' => '09211234567',
                'address' => 'Angeles, Pampanga',
                'is_active' => true,
                'supplies' => [
                    'Sugar',
                    'Honey',
                    'Vanilla Syrup',
                    'Caramel Syrup',
                    'Hazelnut Syrup',
                ],
            ],
            [
                'supplier_name' => 'Artisan Bakery Supply',
                'contact_person' => 'Princess Yusi',
                'email' => 'artisanbakery@gmail.com',
                'phone' => '12956035809',
                'address' => 'Pandacqui, Mexico',
                'is_active' => false,
                'supplies' => [
                    'Sugar',
                    'Coffee Beans - Arabica',
                    'Coffee Beans - Robusta',
                ],
            ],
        ];

        foreach ($suppliers as $supplierData) {
            $supplier = Supplier::query()->updateOrCreate(
                ['supplier_name' => $supplierData['supplier_name']],
                [
                    'contact_person' => $supplierData['contact_person'],
                    'email' => $supplierData['email'],
                    'phone' => $supplierData['phone'],
                    'address' => $supplierData['address'],
                    'is_active' => $supplierData['is_active'],
                ]
            );

            $inventoryIds = Inventory::query()
                ->whereIn('item_name', $supplierData['supplies'])
                ->pluck('id')
                ->all();

            $supplier->inventoryItems()->sync($inventoryIds);
        }
    }
}
