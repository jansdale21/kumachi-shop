<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['admin', 'staff', 'user'] as $roleName) {
            Role::query()->firstOrCreate([
                'role_name' => $roleName,
            ]);
        }
    }
}
