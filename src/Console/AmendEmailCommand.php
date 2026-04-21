<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Console;

use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

final class AmendEmailCommand extends Command
{
    protected $signature = 'user-tools:user-amend-email
                            {identifier? : The user identifier, e.g. email or ID}
                            {--column= : Column to search against}
                            {--email= : New email address to set}';

    protected $description = 'Change a user email address';

    public function handle(): int
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = (string) config('user-tools.user_model');
        $column = (string) ($this->option('column') ?: config('user-tools.lookup_column', 'email'));

        if (! class_exists($userModel)) {
            $this->error("Configured user model [{$userModel}] does not exist.");

            return self::FAILURE;
        }

        $identifier = $this->argument('identifier')
            ?: text(
                label: "Enter the user's {$column} or ID",
                required: true
            );

        // Try to find user by the specified column first
        $user = $userModel::query()->where($column, $identifier)->first();

        // If not found and column is not 'id', try searching by ID as fallback
        if (! $user && $column !== 'id') {
            $user = $userModel::query()->where('id', $identifier)->first();
        }

        if (! $user) {
            $this->error("No user found with {$column} = [{$identifier}]" . ($column !== 'id' ? ' or ID = [' . $identifier . ']' : '') . '.');

            return self::FAILURE;
        }

        $newEmail = (string) $this->option('email');

        if ($newEmail === '') {
            $newEmail = text(
                label: 'Enter the new email address',
                required: true,
                validate: ['email' => ['required', 'email']]
            );
        }

        // Validate email format
        if (! filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address format.');

            return self::FAILURE;
        }

        // Check if email is already taken by another user
        $existingUser = $userModel::query()->where('email', $newEmail)->where('id', '!=', $user->id)->first();
        if ($existingUser) {
            $this->error("Email address [{$newEmail}] is already taken by another user.");

            return self::FAILURE;
        }

        $this->info("Current email: {$user->email}");
        $this->info("New email: {$newEmail}");

        if (! confirm("Change email for user ID [{$user->id}]?", default: false)) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $user->email = $newEmail;
        $user->save();

        $this->info("Email updated for user ID [{$user->id}] to [{$newEmail}].");

        return self::SUCCESS;
    }
}