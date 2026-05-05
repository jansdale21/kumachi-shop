<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_staff_cannot_access_kiosk(): void
    {
        $userRole = Role::query()->create(['role_name' => 'user']);
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $this->actingAs($user)
            ->get(route('kiosk'))
            ->assertRedirect(route('home'));
    }

    public function test_staff_can_access_kiosk(): void
    {
        $staffRole = Role::query()->create(['role_name' => 'staff']);
        $staff = User::factory()->create(['role_id' => $staffRole->id]);

        $this->actingAs($staff)
            ->get(route('kiosk'))
            ->assertRedirect(route('kiosk.menu'));

        $this->actingAs($staff)
            ->get(route('kiosk.menu'))
            ->assertOk()
            ->assertSee('Kiosk Menu');
    }
}
