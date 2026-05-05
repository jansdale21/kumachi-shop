<?php

namespace Tests\Feature\Auth;

use App\Models\PendingRegistration;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));

        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);

        $pending = PendingRegistration::query()->where('email', 'test@example.com')->first();
        $this->assertNotNull($pending);
        $this->assertTrue(Hash::check('StrongPass1!', (string) $pending->password_hash));
    }

    public function test_account_is_created_only_after_verification_link(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
        ])->assertRedirect(route('login', absolute: false));

        $pending = PendingRegistration::query()->where('email', 'test@example.com')->first();
        $this->assertNotNull($pending);

        $response = $this->get(route('register.verify', ['token' => $pending->token], absolute: false));

        $response->assertRedirect(route('login', absolute: false));

        $userRole = Role::query()->where('role_name', 'user')->first();

        $this->assertNotNull($userRole);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role_id' => $userRole->id,
        ]);
        $this->assertNotNull(PendingRegistration::query()->where('email', 'test@example.com')->value('used_at'));
    }
}
