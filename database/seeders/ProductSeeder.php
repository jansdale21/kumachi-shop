<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Americano', 'category' => 'Coffee', 'base_price' => 120, 'availability' => 'available'],
            ['name' => 'Dark Mocha', 'category' => 'Coffee', 'base_price' => 110, 'availability' => 'available'],
            ['name' => 'White Mocha', 'category' => 'Coffee', 'base_price' => 120, 'availability' => 'available'],
            ['name' => 'Caramel Latte', 'category' => 'Coffee', 'base_price' => 130, 'availability' => 'available'],
            ['name' => 'Matcha Latte', 'category' => 'Tea', 'base_price' => 125, 'availability' => 'available'],
            ['name' => 'Chocolate Frappe', 'category' => 'Non-Coffee', 'base_price' => 140, 'availability' => 'available'],
        ];

        foreach ($products as $item) {
            $categoryId = Category::query()
                ->where('name', $item['category'])
                ->value('id');

            if (! $categoryId) {
                continue;
            }

            Product::query()->updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $categoryId,
                    'image_path' => null,
                    'base_price' => $item['base_price'],
                    'availability' => $item['availability'],
                ]
            );
        }
    }
}
