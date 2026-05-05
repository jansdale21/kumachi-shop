<?php

namespace App\Console\Commands;

use App\Services\BrevoMailer;
use Illuminate\Console\Command;
use Throwable;

class MailTestCommand extends Command
{
    protected $signature = 'kumachi:mail-test {email : Recipient email address}';

    protected $description = 'Send a test message through the same path as registration (BrevoMailer)';

    public function handle(BrevoMailer $mailer): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        try {
            $mailer->sendRegistrationWelcome($email, 'Mail test');
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Sent. Check inbox/spam for: '.$email);

        return self::SUCCESS;
    }
}
