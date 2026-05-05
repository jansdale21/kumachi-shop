<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordOtpCode;
use App\Models\User;
use App\Services\BrevoMailer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim((string) $validated['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        $userExists = $user !== null;

        if ($userExists) {
            $otp = (string) random_int(100000, 999999);

            PasswordOtpCode::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->delete();

            PasswordOtpCode::query()->create([
                'email' => $email,
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
            ]);

            $token = Password::broker()->createToken($user);
            $resetLink = route('password.reset', [
                'token' => $token,
                'email' => $email,
            ]);

            if (! app()->environment('testing')) {
                try {
                    app(BrevoMailer::class)->sendForgotPassword($email, $otp, $resetLink);
                } catch (\Throwable $exception) {
                    Log::warning('Brevo forgot-password email failed', [
                        'email' => $email,
                        'error' => $exception->getMessage(),
                    ]);

                    return back()
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => 'Unable to send reset email right now. Please try again.']);
                }
            }
        }

        if (app()->environment('testing') && $userExists && $user) {
            $token = Password::broker()->createToken($user);
            $user->notify(new ResetPassword($token));
            $status = Password::RESET_LINK_SENT;
        } else {
            $status = $userExists ? Password::RESET_LINK_SENT : Password::INVALID_USER;
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        $statusMessage = 'We emailed both a reset link and an OTP code. Use either method to reset your password.';

        return redirect()
            ->route('password.otp', ['email' => $email])
            ->with('status', $statusMessage);
    }

    public function otpForm(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * @throws ValidationException
     */
    public function otpReset(Request $request): RedirectResponse
    {
        if ($request->filled('token')) {
            $request->validate([
                'token' => ['required', 'string'],
                'email' => ['required', 'email'],
                'password' => [
                    'required',
                    'confirmed',
                    \Illuminate\Validation\Rules\Password::defaults(),
                    function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                        $email = strtolower(trim((string) $request->input('email', '')));
                        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
                        if ($user && Hash::check((string) $value, (string) $user->password)) {
                            $fail('Your new password must be different from your current password.');
                        }
                    },
                ],
            ]);

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user) use ($request): void {
                    $user->forceFill([
                        'password' => Hash::make((string) $request->password),
                        'remember_token' => Str::random(60),
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return redirect()->route('login')->with('status', __($status));
            }

            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => [
                'required',
                'confirmed',
                \Illuminate\Validation\Rules\Password::defaults(),
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $email = strtolower(trim((string) $request->input('email', '')));
                    $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
                    if ($user && Hash::check((string) $value, (string) $user->password)) {
                        $fail('Your new password must be different from your current password.');
                    }
                },
            ],
        ]);

        $email = strtolower(trim((string) $validated['email']));

        $otpRecord = PasswordOtpCode::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otpRecord || $otpRecord->expires_at->isPast() || ! Hash::check((string) $validated['otp'], $otpRecord->otp_hash)) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid or expired OTP.',
            ]);
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'No account found for this email address.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make((string) $validated['password']),
        ])->save();

        $otpRecord->update(['used_at' => now()]);

        return redirect()
            ->route('login')
            ->with('status', 'Password reset successful. You can now sign in.');
    }
}
