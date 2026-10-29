<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ResetLocalAdminPassword extends Command
{
    protected $signature = 'app:reset-local-admin-password {email : Existing administrator email address}';

    protected $description = 'Privately reset an existing local administrator password';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('This command is only available in the local environment.');

            return self::FAILURE;
        }

        $admin = User::query()
            ->where('email', (string) $this->argument('email'))
            ->where('role', 'admin')
            ->where('is_active', true)
            ->first();

        if ($admin === null) {
            $this->error('No active administrator exists with that email address.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('New password (12+ characters, mixed case, number, symbol)');
        $confirmation = (string) $this->secret('Confirm new password');

        try {
            validator(
                ['password' => $password, 'password_confirmation' => $confirmation],
                ['password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()]],
            )->validate();
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        $admin->password = $password;
        $admin->save();

        $this->info('Administrator password updated. Sign in with the new password.');

        return self::SUCCESS;
    }
}
