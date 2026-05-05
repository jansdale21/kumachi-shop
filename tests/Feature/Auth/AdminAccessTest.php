<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure non-admin users cannot access admin pages.
     */
    public function test_non_admin_users_are_redirected_to_home_when_accessing_admin_dashboard(): void
    {
        $userRole = Role::query()->create([
            'role_name' => 'user',
        ]);

        $user = User::factory()->create([
            'role_id' => $userRole->id,
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect(route('home'));
    }

    public function test_admin_users_can_access_admin_dashboard(): void
    {
        $adminRole = Role::query()->create([
            'role_name' => 'admin',
        ]);

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_admin_users_are_redirected_to_admin_dashboard_from_root(): void
    {
        $adminRole = Role::query()->create([
            'role_name' => 'admin',
        ]);

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect(route('admin.dashboard'));
    }
}
