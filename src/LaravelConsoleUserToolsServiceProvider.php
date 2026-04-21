<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools;

use Newtovaux\LaravelConsoleUserTools\Console\AmendEmailCommand;
use Newtovaux\LaravelConsoleUserTools\Console\ChangeUserPasswordCommand;
use Newtovaux\LaravelConsoleUserTools\Console\CreatePasswordCommand;
use Newtovaux\LaravelConsoleUserTools\Console\ListUsersCommand;
use Illuminate\Support\ServiceProvider;

final class LaravelConsoleUserToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/user-tools.php',
            'user-tools'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/user-tools.php' => config_path('user-tools.php'),
            ], 'user-tools-config');

            $this->commands([
                AmendEmailCommand::class,
                CreatePasswordCommand::class,
                ChangeUserPasswordCommand::class,
                ListUsersCommand::class,
            ]);
        }
    }
}
