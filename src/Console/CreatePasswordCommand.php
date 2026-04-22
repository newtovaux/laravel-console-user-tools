<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Console;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Newtovaux\LaravelConsoleUserTools\Support\PasswordService;

final class CreatePasswordCommand extends Command
{
    public function __construct(
        private readonly PasswordService $passwords
    ) {
        parent::__construct();
    }

    protected $signature = 'user-tools:create
                            {--length= : Password length}
                            {--no-symbols : Exclude symbols}
                            {--copy : Attempt to copy to clipboard (not implemented)}';

    protected $description = 'Generate a secure password';

    public function handle(): int
    {
        $length = (int) ($this->option('length') ?: config('user-tools.generated_length', 20));

        try {
            $password = $this->passwords->generate(
                $length,
                includeSymbols: ! (bool) $this->option('no-symbols')
            );
            $this->passwords->ensureValid($password);
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

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
}
