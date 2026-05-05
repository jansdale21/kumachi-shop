<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_role_is_case_insensitive_and_trims_input(): void
    {
        $adminRole = Role::query()->create(['role_name' => 'admin']);
        $user = User::factory()->create(['role_id' => $adminRole->id]);

        $this->assertTrue($user->hasRole('  AdMiN  '));
    }

    public function test_has_role_returns_false_when_user_has_no_role(): void
    {
        $user = User::factory()->create(['role_id' => null]);

        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_is_admin_and_is_staff_helpers_match_assigned_role(): void
    {
        $staffRole = Role::query()->create(['role_name' => 'staff']);
        $user = User::factory()->create(['role_id' => $staffRole->id]);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isStaff());
    }
}
