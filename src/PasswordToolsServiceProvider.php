<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools;

use Newtovaux\LaravelConsoleUserTools\Console\ChangeUserPasswordCommand;
use Newtovaux\LaravelConsoleUserTools\Console\CreatePasswordCommand;
use Illuminate\Support\ServiceProvider;

final class PasswordToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/password-tools.php',
            'password-tools'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/password-tools.php' => config_path('password-tools.php'),
            ], 'password-tools-config');

            $this->commands([
                CreatePasswordCommand::class,
                ChangeUserPasswordCommand::class,
            ]);
        }
    }
}
