<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->where('email', 'test@example.com')->first();
        if (! $user) {
            return;
        }

        PaymentMethod::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'label' => 'Primary Visa',
            ],
            [
                'cardholder_name' => 'Test User',
                'card_brand' => 'Visa',
                'card_last4' => '4242',
                'cvv_hash' => Hash::make('123'),
                'exp_month' => 12,
                'exp_year' => 2028,
                'is_default' => true,
            ]
        );
    }
}
