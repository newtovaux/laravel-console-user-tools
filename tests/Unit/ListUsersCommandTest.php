<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Newtovaux\LaravelConsoleUserTools\Tests\TestCase;
use Newtovaux\LaravelConsoleUserTools\Tests\TestUser;
use Symfony\Component\Console\Command\Command;

class ListUsersCommandTest extends TestCase
{
    public function test_command_fails_with_nonexistent_user_model(): void
    {
        $this->app['config']->set('user-tools.user_model', 'NonExistentModel');

        $exitCode = Artisan::call('user-tools:list-users');

        $output = Artisan::output();

        $this->assertStringContainsString('Configured user model [NonExistentModel] does not exist.', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }

    public function test_command_shows_no_users_message_when_no_users_exist(): void
    {
        $exitCode = Artisan::call('user-tools:list-users');

        $output = Artisan::output();

        $this->assertStringContainsString('No users found.', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function test_command_lists_users_in_table_format(): void
    {
        // Create test users
        TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        TestUser::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
        ]);

        $exitCode = Artisan::call('user-tools:list-users');

        $output = Artisan::output();

        $this->assertStringContainsString('ID', $output);
        $this->assertStringContainsString('Email', $output);
        $this->assertStringContainsString('john@example.com', $output);
        $this->assertStringContainsString('jane@example.com', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function test_command_displays_correct_user_data(): void
    {
        $user = TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $exitCode = Artisan::call('user-tools:list-users');

        $output = Artisan::output();

        $this->assertStringContainsString((string) $user->id, $output);
        $this->assertStringContainsString('test@example.com', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function test_command_handles_multiple_users_correctly(): void
    {
        // Create multiple users
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = TestUser::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password123'),
            ]);
        }

        $exitCode = Artisan::call('user-tools:list-users');

        $output = Artisan::output();

        // Check that all users are listed
        foreach ($users as $user) {
            $this->assertStringContainsString($user->email, $output);
            $this->assertStringContainsString((string) $user->id, $output);
        }

        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function test_command_honors_limit_option(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            TestUser::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password123'),
            ]);
        }

        $exitCode = Artisan::call('user-tools:list-users', ['--limit' => 2]);
        $output = Artisan::output();

        $this->assertStringContainsString('user1@example.com', $output);
        $this->assertStringContainsString('user2@example.com', $output);
        $this->assertStringNotContainsString('user3@example.com', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function test_command_fails_with_invalid_limit_option(): void
    {
        $exitCode = Artisan::call('user-tools:list-users', ['--limit' => 0]);
        $output = Artisan::output();

        $this->assertStringContainsString('The --limit option must be a positive integer.', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }

    public function test_command_omits_unselected_columns_from_output(): void
    {
        TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $exitCode = Artisan::call('user-tools:list-users');
        $output = Artisan::output();

        $this->assertStringNotContainsString('Test User', $output);
        $this->assertStringNotContainsString('password123', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }
}
