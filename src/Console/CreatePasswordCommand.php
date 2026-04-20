<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class CreatePasswordCommand extends Command
{
    protected $signature = 'password-tools:create
                            {--length= : Password length}
                            {--no-symbols : Exclude symbols}
                            {--copy : Attempt to copy to clipboard (not implemented)}';

    protected $description = 'Generate a secure password';

    public function handle(): int
    {
        $length = (int) ($this->option('length') ?: config('password-tools.generated_length', 20));

        if ($length < 8) {
            $this->error('Password length must be at least 8 characters.');

            return self::FAILURE;
        }

        $password = $this->generatePassword(
            $length,
            includeSymbols: ! (bool) $this->option('no-symbols')
        );

        $this->newLine();
        $this->info('Generated password:');
        $this->line($password);
        $this->newLine();

        $this->comment('Store it safely. It will not be shown again by this command.');

        if ((bool) $this->option('copy')) {
            $this->warn('Clipboard copy is not implemented in this package.');
        }

        return self::SUCCESS;
    }

    private function generatePassword(int $length, bool $includeSymbols = true): string
    {
        $letters = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $numbers = '23456789';
        $symbols = '!@#$%^&*()-_=+[]{}?';
        $pool = $letters . $numbers . ($includeSymbols ? $symbols : '');

        $required = [
            $letters[random_int(0, strlen($letters) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
        ];

        if ($includeSymbols) {
            $required[] = $symbols[random_int(0, strlen($symbols) - 1)];
        }

        while (count($required) < $length) {
            $required[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        shuffle($required);

        return implode('', array_slice($required, 0, $length));
    }
}
