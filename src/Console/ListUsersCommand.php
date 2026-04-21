<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Console;

use Illuminate\Console\Command;

final class ListUsersCommand extends Command
{
    protected $signature = 'user-tools:list-users';

    protected $description = 'List all users with their ID and email';

    public function handle(): int
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = (string) config('user-tools.user_model');

        if (! class_exists($userModel)) {
            $this->error("Configured user model [{$userModel}] does not exist.");

            return self::FAILURE;
        }

        $users = $userModel::query()->select('id', 'email')->get();

        if ($users->isEmpty()) {
            $this->info('No users found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Email'],
            $users->map(function ($user) {
                return [$user->id, $user->email];
            })
        );

        return self::SUCCESS;
    }
}