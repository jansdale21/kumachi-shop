<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class BrevoMailer
{
    public function sendRegistrationVerification(string $toEmail, string $name, string $verifyLink): void
    {
        $subject = 'Verify your Kumachi account';
        $safeName = e($name !== '' ? $name : 'there');
        $safeVerifyLink = e($verifyLink);

        $html = <<<HTML
<p>Hi {$safeName},</p>
<p>Please verify your email to complete your Kumachi registration.</p>
<p><a href="{$safeVerifyLink}">Verify My Account</a></p>
<p>This link expires in 60 minutes.</p>
HTML;

        $this->send($toEmail, $subject, $html);
    }

    public function sendRegistrationWelcome(string $toEmail, string $name): void
    {
        $subject = 'Welcome to Kumachi';
        $safeName = e($name !== '' ? $name : 'there');

        $html = <<<HTML
<p>Hi {$safeName},</p>
<p>Your Kumachi account has been created successfully.</p>
<p>You can now sign in and start placing your orders.</p>
<p>Thank you,<br>Kumachi Team</p>
HTML;

        $this->send($toEmail, $subject, $html);
    }

    public function sendForgotPassword(string $toEmail, string $otp, string $resetLink): void
    {
        $subject = 'Kumachi Password Reset Instructions';
        $safeOtp = e($otp);
        $safeResetLink = e($resetLink);

        $html = <<<HTML
<p>We received a request to reset your Kumachi password.</p>
<p><strong>OTP:</strong> {$safeOtp} (valid for 10 minutes)</p>
<p>You can also reset via this link:</p>
<p><a href="{$safeResetLink}">Reset Password</a></p>
<p>If you did not request this, please ignore this email.</p>
HTML;

        $this->send($toEmail, $subject, $html);
    }

    private function send(string $toEmail, string $subject, string $html): void
    {
        $mode = (string) config('services.brevo.mail_transport', 'laravel');

        if ($mode === 'api') {
            $this->sendViaHttpApi($toEmail, $subject, $html);

            return;
        }

        if ($mode !== 'laravel') {
            throw new RuntimeException('Invalid BREVO_MAIL_TRANSPORT. Use laravel or api.');
        }

        try {
            $this->sendViaLaravelMail($toEmail, $subject, $html);
        } catch (Throwable $smtpException) {
            Log::warning('Brevo SMTP (Laravel Mail) failed', [
                'to' => $toEmail,
                'error' => $smtpException->getMessage(),
            ]);

            $apiKey = (string) config('services.brevo.api_key', '');
            $fallback = filter_var(config('services.brevo.fallback_to_api', true), FILTER_VALIDATE_BOOLEAN);

            if (
                $fallback
                && str_starts_with($apiKey, 'xkeysib-')
            ) {
                Log::info('Retrying email via Brevo REST API after SMTP failure.');
                $this->sendViaHttpApi($toEmail, $subject, $html);

                return;
            }

            throw new RuntimeException(
                $smtpException->getMessage()
                .' — If this persists on XAMPP/Windows, try MAIL_PORT=465 and MAIL_ENCRYPTION=ssl, '
                .'or add an API key (xkeysib-…) from Brevo and set BREVO_MAIL_TRANSPORT=api.',
                0,
                $smtpException
            );
        }
    }

    private function sendViaLaravelMail(string $toEmail, string $subject, string $html): void
    {
        Mail::html($html, function ($message) use ($toEmail, $subject): void {
            $message->from(
                (string) config('services.brevo.from_email', config('mail.from.address')),
                (string) config('services.brevo.from_name', config('mail.from.name')),
            );
            $message->to($toEmail)->subject($subject);
        });
    }

    private function sendViaHttpApi(string $toEmail, string $subject, string $html): void
    {
        $apiKey = (string) config('services.brevo.api_key', '');

        if ($apiKey === '') {
            throw new RuntimeException('Brevo API key is not configured (BREVO_API_KEY).');
        }

        if (str_starts_with($apiKey, 'xsmtpsib-')) {
            throw new RuntimeException(
                'BREVO_API_KEY is an SMTP relay key (xsmtpsib). REST API needs the v3 key (xkeysib-…) from Brevo → SMTP & API → API keys. '
                .'Either paste that key here and use BREVO_MAIL_TRANSPORT=api, or keep your SMTP key only in MAIL_PASSWORD and use BREVO_MAIL_TRANSPORT=laravel.'
            );
        }

        $response = Http::timeout(15)
            ->withHeaders([
                'accept' => 'application/json',
                'api-key' => $apiKey,
                'content-type' => 'application/json',
            ])
            ->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => (string) config('services.brevo.from_name', 'Kumachi'),
                    'email' => (string) config('services.brevo.from_email', 'no-reply@example.com'),
                ],
                'to' => [
                    ['email' => $toEmail],
                ],
                'subject' => $subject,
                'htmlContent' => $html,
            ]);

        if (! $response->successful()) {
            $body = $response->body();
            $detail = $body;
            $decoded = json_decode($body, true);
            if (is_array($decoded) && isset($decoded['message'])) {
                $detail = (string) $decoded['message'];
            }

            throw new RuntimeException(
                'Brevo send failed (HTTP '.$response->status().'): '.$detail
            );
        }
    }
}
