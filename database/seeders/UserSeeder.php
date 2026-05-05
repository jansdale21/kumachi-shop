<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()
            ->whereIn('role_name', ['admin', 'staff', 'user'])
            ->pluck('id', 'role_name');

        $seedUsers = [
            [
                'email' => 'admin@kumachi.test',
                'name' => 'Admin User',
                'phone' => '09170000010',
                'role_name' => 'admin',
            ],
            [
                'email' => 'staff@kumachi.test',
                'name' => 'Staff User',
                'phone' => '09170000020',
                'role_name' => 'staff',
            ],
            [
                'email' => 'test@example.com',
                'name' => 'Test User',
                'phone' => '09170000001',
                'role_name' => 'user',
            ],
        ];

        foreach ($seedUsers as $seedUser) {
            $roleId = $roles->get($seedUser['role_name']);
            if (! $roleId) {
                continue;
            }

            User::query()->updateOrCreate(
                ['email' => $seedUser['email']],
                [
                    'name' => $seedUser['name'],
                    'phone' => $seedUser['phone'],
                    'role_id' => $roleId,
                    'status' => true,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
