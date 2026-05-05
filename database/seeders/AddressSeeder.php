<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
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

        $addresses = [
            [
                'full_name' => 'Test User',
                'phone' => '09170000001',
                'street' => '123 Main Street',
                'city' => 'Cebu City',
            ],
            [
                'full_name' => 'Test User',
                'phone' => '09170000002',
                'street' => '45 Mango Avenue',
                'city' => 'Mandaue City',
            ],
        ];

        foreach ($addresses as $address) {
            Address::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'street' => $address['street'],
                ],
                [
                    'full_name' => $address['full_name'],
                    'phone' => $address['phone'],
                    'city' => $address['city'],
                    'is_default' => $address['street'] === '123 Main Street',
                ]
            );
        }
    }
}
