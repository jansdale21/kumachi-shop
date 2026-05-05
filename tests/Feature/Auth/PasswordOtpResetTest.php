<?php

namespace Tests\Feature\Auth;

use App\Models\PasswordOtpCode;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordOtpResetTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $email = 'otp-user@example.com'): User
    {
        $role = Role::query()->firstOrCreate([
            'role_name' => 'user',
        ]);

        return User::factory()->create([
            'email' => $email,
            'role_id' => $role->id,
        ]);
    }

    public function test_forgot_password_creates_otp_record_for_existing_user(): void
    {
        $user = $this->createUser('otp-request@example.com');

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('password_otp_codes', [
            'email' => strtolower($user->email),
            'used_at' => null,
        ]);
    }

    public function test_password_can_be_reset_with_valid_otp(): void
    {
        $user = $this->createUser('otp-reset@example.com');

        $otpRecord = PasswordOtpCode::query()->create([
            'email' => strtolower($user->email),
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
            'used_at' => null,
        ]);

        $response = $this->post(route('password.store'), [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));

        $this->assertNotNull($otpRecord->fresh()?->used_at);
    }

    public function test_password_reset_rejects_invalid_otp(): void
    {
        $user = $this->createUser('otp-invalid@example.com');

        PasswordOtpCode::query()->create([
            'email' => strtolower($user->email),
            'otp_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
            'used_at' => null,
        ]);

        $response = $this->from(route('password.otp'))->post(route('password.store'), [
            'email' => $user->email,
            'otp' => '000000',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('password.otp'));
        $response->assertSessionHasErrors('otp');
    }
}
