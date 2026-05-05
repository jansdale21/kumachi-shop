<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class SetUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:set-role
                            {email : Email address of the user}
                            {role : Role name (admin or user)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user by email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $roleName = strtolower((string) $this->argument('role'));

        if (! in_array($roleName, ['admin', 'user'], true)) {
            $this->error('Invalid role. Allowed roles: admin, user.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("User not found for email: {$email}");

            return self::FAILURE;
        }

        $role = Role::query()->firstOrCreate([
            'role_name' => $roleName,
        ]);

        $user->role_id = $role->id;
        $user->save();

        $this->info("Assigned role '{$roleName}' to {$email}.");

        return self::SUCCESS;
    }
}
