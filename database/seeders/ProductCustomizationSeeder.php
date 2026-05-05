<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Database\Seeder;

class ProductCustomizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addonIds = Addon::query()->pluck('id')->all();

        Product::query()->get()->each(function (Product $product) use ($addonIds) {
            $sizes = [
                ['size_name' => 'Small', 'price_adjustment' => 0],
                ['size_name' => 'Medium', 'price_adjustment' => 0.50],
                ['size_name' => 'Large', 'price_adjustment' => 1.00],
            ];

            foreach ($sizes as $size) {
                ProductSize::query()->firstOrCreate([
                    'product_id' => $product->id,
                    'size_name' => $size['size_name'],
                ], [
                    'price_adjustment' => $size['price_adjustment'],
                ]);
            }

            if ($addonIds !== []) {
                $product->addons()->syncWithoutDetaching($addonIds);
            }
        });
    }
}
