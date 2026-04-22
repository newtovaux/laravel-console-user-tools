<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class ListUsersCommand extends Command
{
    private const int PAGE_SIZE = 100;

    protected $signature = 'user-tools:list-users
                            {--limit= : Maximum number of users to display}';

    protected $description = 'List all users with their ID and email';

    public function handle(): int
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $userModel */
        $userModel = (string) config('user-tools.user_model');

        if (! class_exists($userModel)) {
            $this->error("Configured user model [{$userModel}] does not exist.");

            return self::FAILURE;
        }

        $limit = $this->parseLimit();

        if ($limit === false) {
            return self::FAILURE;
        }

        if ($limit === null && $this->output->isVerbose()) {
            $this->line(sprintf(
                'Streaming users in pages of %d records.',
                self::PAGE_SIZE
            ));
        }

        $rowsShown = 0;

        $userModel::query()
            ->select('id', 'email')
            ->orderBy('id')
            ->chunkById(self::PAGE_SIZE, function (Collection $users) use (&$rowsShown, $limit): bool {
                $remaining = $limit === null ? null : $limit - $rowsShown;

                if ($remaining !== null && $remaining <= 0) {
                    return false;
                }

                $page = $remaining === null
                    ? $users
                    : $users->take($remaining);

                if ($page->isEmpty()) {
                    return false;
                }

                $this->table(
                    ['ID', 'Email'],
                    $page->map(static function ($user): array {
                        return [$user->id, $user->email];
                    })
                );

                $rowsShown += $page->count();

                if ($limit !== null && $rowsShown >= $limit) {
                    return false;
                }

                $this->newLine();

                return true;
            }, column: 'id');

        if ($rowsShown === 0) {
            $this->info('No users found.');

            return self::SUCCESS;
        }

        return self::SUCCESS;
    }

    private function parseLimit(): int|false|null
    {
        $limit = $this->option('limit');

        if ($limit === null || $limit === '') {
            return null;
        }

        if (! is_numeric($limit) || (int) $limit < 1) {
            $this->error('The --limit option must be a positive integer.');

            return false;
        }

        return (int) $limit;
    }
}
