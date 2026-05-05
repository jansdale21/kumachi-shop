<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        $adminRole = Role::query()->firstOrCreate([
            'role_name' => 'admin',
        ]);

        return User::factory()->create([
            'role_id' => $adminRole->id,
            'status' => true,
        ]);
    }

    private function createRegularUser(): User
    {
        $userRole = Role::query()->firstOrCreate([
            'role_name' => 'user',
        ]);

        return User::factory()->create([
            'role_id' => $userRole->id,
            'status' => true,
        ]);
    }

    public function test_admin_can_view_user_listing_page(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_user_management_page(): void
    {
        $user = $this->createRegularUser();

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertRedirect(route('home'));
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->createAdminUser();
        $role = Role::query()->firstOrCreate([
            'role_name' => 'user',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Created User',
            'email' => 'created.user@example.com',
            'phone' => '09123456789',
            'role_id' => $role->id,
            'status' => '1',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Created User',
            'email' => 'created.user@example.com',
            'role_id' => $role->id,
            'status' => true,
        ]);
    }

    public function test_admin_cannot_change_password_when_updating_user_profile(): void
    {
        $admin = $this->createAdminUser();
        $role = Role::query()->firstOrCreate([
            'role_name' => 'user',
        ]);
        $managedUser = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'before.update@example.com',
            'password' => 'password123',
            'status' => true,
        ]);
        $originalPasswordHash = $managedUser->password;

        $response = $this->actingAs($admin)->put(route('admin.users.update', $managedUser), [
            'name' => 'Updated Name',
            'email' => 'after.update@example.com',
            'phone' => '09998887777',
            'role_id' => $role->id,
            'status' => '0',
            'password' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $managedUser->refresh();

        $this->assertSame('Updated Name', $managedUser->name);
        $this->assertSame('after.update@example.com', $managedUser->email);
        $this->assertSame('09998887777', $managedUser->phone);
        $this->assertFalse((bool) $managedUser->status);
        $this->assertSame($originalPasswordHash, $managedUser->password);
    }

    public function test_admin_cannot_delete_self_account(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'You cannot delete your own account.');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_other_user_account(): void
    {
        $admin = $this->createAdminUser();
        $managedUser = $this->createRegularUser();

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $managedUser));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSoftDeleted('users', ['id' => $managedUser->id]);
    }
}
