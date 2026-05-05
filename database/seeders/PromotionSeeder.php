<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $promotions = [
            ['code' => 'WELCOME10', 'discount_value' => 10, 'expires_at' => now()->addDays(30)],
            ['code' => 'KUMACHI15', 'discount_value' => 15, 'expires_at' => now()->addDays(45)],
            ['code' => 'FREESHOT20', 'discount_value' => 20, 'expires_at' => now()->addDays(60)],
            ['code' => 'EXCLUSIVE25', 'discount_value' => 25, 'expires_at' => now()->addDays(90)],
        ];

        foreach ($promotions as $promotion) {
            Promotion::query()->updateOrCreate(
                ['code' => $promotion['code']],
                [
                    'discount_value' => $promotion['discount_value'],
                    'expires_at' => $promotion['expires_at'],
                ]
            );
        }
    }
}
