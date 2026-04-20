<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class ChangeUserPasswordCommand extends Command
{
    protected $signature = 'password-tools:user-password
                            {identifier? : The user identifier, e.g. email}
                            {--column= : Column to search against}
                            {--password= : Password to set}
                            {--generate : Generate a password instead of entering one}
                            {--length=20 : Length of generated password}';

    protected $description = 'Change a user password';

    public function handle(): int
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = (string) config('password-tools.user_model');
        $column = (string) ($this->option('column') ?: config('password-tools.lookup_column', 'email'));

        if (! class_exists($userModel)) {
            $this->error("Configured user model [{$userModel}] does not exist.");

            return self::FAILURE;
        }

        $identifier = $this->argument('identifier')
            ?: text(
                label: "Enter the user's {$column}",
                required: true
            );

        $user = $userModel::query()->where($column, $identifier)->first();

        if (! $user) {
            $this->error("No user found where {$column} = [{$identifier}].");

            return self::FAILURE;
        }

        $password = (string) $this->option('password');

        if ($password === '') {
            if ((bool) $this->option('generate')) {
                $password = $this->generatePassword((int) $this->option('length'));
                $this->warn("Generated password for {$identifier}: {$password}");
            } else {
                $password = password(
                    label: 'Enter the new password',
                    required: true,
                    validate: ['password' => ['required', 'string', 'min:8']]
                );

                $confirmation = password(
                    label: 'Confirm the new password',
                    required: true
                );

                if ($password !== $confirmation) {
                    $this->error('Passwords do not match.');

                    return self::FAILURE;
                }
            }
        }

        if (! confirm("Change password for user [{$identifier}]?", default: false)) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password updated for user [{$identifier}].");

        return self::SUCCESS;
    }

    private function generatePassword(int $length): string
    {
        $letters = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $numbers = '23456789';
        $symbols = '!@#$%^&*()-_=+[]{}?';
        $pool = $letters . $numbers . $symbols;

        $chars = [
            $letters[random_int(0, strlen($letters) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        while (count($chars) < max($length, 8)) {
            $chars[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        shuffle($chars);

        return implode('', array_slice($chars, 0, max($length, 8)));
    }
}
