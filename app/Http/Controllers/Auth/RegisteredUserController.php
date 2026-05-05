<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\Role;
use App\Models\User;
use App\Services\BrevoMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = strtolower(trim((string) $validated['email']));
        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This email is already registered. Please sign in or use forgot password.',
            ]);
        }

        $token = Str::random(64);
        $pending = PendingRegistration::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->latest()
            ->first();

        if ($pending) {
            $pending->update([
                'name' => (string) $validated['name'],
                'password_hash' => Hash::make((string) $validated['password']),
                'token' => $token,
                'expires_at' => now()->addHour(),
                'used_at' => null,
            ]);
        } else {
            PendingRegistration::query()->create([
                'name' => (string) $validated['name'],
                'email' => $email,
                'password_hash' => Hash::make((string) $validated['password']),
                'token' => $token,
                'expires_at' => now()->addHour(),
            ]);
        }

        $verifyLink = route('register.verify', ['token' => $token]);

        $emailFailed = false;
        if (! app()->environment('testing')) {
            try {
                app(BrevoMailer::class)->sendRegistrationVerification($email, (string) $validated['name'], $verifyLink);
            } catch (\Throwable $exception) {
                $emailFailed = true;
                Log::error('Brevo registration verification email failed', [
                    'email' => $email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($emailFailed) {
            return back()
                ->withInput($request->only('name', 'email'))
                ->withErrors([
                    'email' => 'Unable to send verification email right now. Please try again.',
                ]);
        }

        return redirect()
            ->route('login')
            ->with('status', 'Please verify your email to finish creating your account.');
    }

    public function verify(string $token): RedirectResponse
    {
        $pending = PendingRegistration::query()
            ->where('token', $token)
            ->whereNull('used_at')
            ->first();

        if (! $pending || $pending->expires_at->isPast()) {
            return redirect()
                ->route('register')
                ->withErrors([
                    'email' => 'Verification link is invalid or expired. Please register again.',
                ]);
        }

        if (User::query()->whereRaw('LOWER(email) = ?', [strtolower((string) $pending->email)])->exists()) {
            $pending->update(['used_at' => now()]);

            return redirect()
                ->route('login')
                ->with('status', 'Email is already registered. Please sign in.');
        }

        $defaultRole = Role::query()->firstOrCreate([
            'role_name' => 'user',
        ]);

        $userId = DB::table('users')->insertGetId([
            'role_id' => $defaultRole->id,
            'name' => (string) $pending->name,
            'email' => strtolower((string) $pending->email),
            'password' => (string) $pending->password_hash,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::query()->findOrFail($userId);

        $pending->update(['used_at' => now()]);

        try {
            if (! app()->environment('testing')) {
                app(BrevoMailer::class)->sendRegistrationWelcome((string) $user->email, (string) $user->name);
            }
        } catch (\Throwable $exception) {
            Log::warning('Brevo registration welcome email failed after verification', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('login')
            ->with('status', 'Account verified and created. You can now sign in.');
    }
}
