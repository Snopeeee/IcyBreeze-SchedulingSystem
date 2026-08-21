<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin
        {email? : Administrator email address}
        {--name=IcyBreeze Admin : Administrator display name}
        {--phone= : Administrator phone number}
        {--password= : Password for non-interactive setup}';

    protected $description = 'Create or securely update an active IcyBreeze administrator';

    public function handle(): int
    {
        $email = Str::lower((string) ($this->argument('email') ?: $this->ask('Administrator email address')));
        $password = (string) ($this->option('password') ?: $this->secret('Password (at least 12 characters with mixed case, a number, and a symbol)'));

        try {
            validator(
                ['email' => $email, 'name' => $this->option('name'), 'phone' => $this->option('phone'), 'password' => $password],
                [
                    'email' => ['required', 'email', 'max:160'],
                    'name' => ['required', 'string', 'max:120'],
                    'phone' => ['nullable', 'string', 'max:30'],
                    'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
                ],
            )->validate();
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $this->option('name'),
                'phone' => $this->option('phone') ?: null,
                'password' => $password,
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        $this->info("Administrator {$admin->email} is ready.");

        return self::SUCCESS;
    }
}
