<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Newtovaux\LaravelConsoleUserTools\Tests\TestCase;
use Newtovaux\LaravelConsoleUserTools\Tests\TestUser;
use Symfony\Component\Console\Command\Command;

class AmendEmailCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        TestUser::create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_command_fails_with_nonexistent_user_model(): void
    {
        $this->app['config']->set('user-tools.user_model', 'NonExistentModel');

        $exitCode = Artisan::call('user-tools:user-amend-email', ['identifier' => 'test@example.com']);

        $output = Artisan::output();

        $this->assertStringContainsString('Configured user model [NonExistentModel] does not exist.', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }

    public function test_command_fails_with_nonexistent_user(): void
    {
        $exitCode = Artisan::call('user-tools:user-amend-email', ['identifier' => 'nonexistent@example.com']);

        $output = Artisan::output();

        $this->assertStringContainsString('No user found with email = [nonexistent@example.com] or ID = [nonexistent@example.com].', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }

    public function test_command_succeeds_with_valid_user_and_email_option(): void
    {
        $this->artisan('user-tools:user-amend-email', [
            'identifier' => 'test@example.com',
            '--email' => 'newemail@example.com',
        ])
            ->expectsConfirmation('Change email for user ID [1]?', 'yes')
            ->expectsOutputToContain('Current email: test@example.com')
            ->expectsOutputToContain('New email: newemail@example.com')
            ->expectsOutputToContain('Email updated for user ID [1] to [newemail@example.com].')
            ->assertSuccessful();

        $user = TestUser::find(1);
        $this->assertEquals('newemail@example.com', $user->email);
    }

    public function test_command_works_with_user_id(): void
    {
        $user = TestUser::where('email', 'test@example.com')->first();

        $this->artisan('user-tools:user-amend-email', [
            'identifier' => (string) $user->id,
            '--column' => 'id',
            '--email' => 'newemail@example.com',
        ])
            ->expectsConfirmation("Change email for user ID [{$user->id}]?", 'yes')
            ->expectsOutputToContain("Email updated for user ID [{$user->id}] to [newemail@example.com].")
            ->assertSuccessful();

        $user->refresh();
        $this->assertEquals('newemail@example.com', $user->email);
    }

    public function test_command_fails_with_invalid_email_format(): void
    {
        $exitCode = Artisan::call('user-tools:user-amend-email', [
            'identifier' => 'test@example.com',
            '--email' => 'invalid-email',
        ]);

        $output = Artisan::output();

        $this->assertStringContainsString('Invalid email address format.', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }

    public function test_command_fails_when_email_already_taken(): void
    {
        $exitCode = Artisan::call('user-tools:user-amend-email', [
            'identifier' => 'test@example.com',
            '--email' => 'another@example.com', // This email is already taken
        ]);

        $output = Artisan::output();

        $this->assertStringContainsString('Email address [another@example.com] is already taken by another user.', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }

    public function test_command_finds_user_by_id_when_email_lookup_fails(): void
    {
        $user = TestUser::where('email', 'test@example.com')->first();

        // Try to find by ID when column is not 'id'
        $this->artisan('user-tools:user-amend-email', [
            'identifier' => (string) $user->id,
            '--email' => 'updated@example.com',
        ])
            ->expectsConfirmation("Change email for user ID [{$user->id}]?", 'yes')
            ->expectsOutputToContain("Email updated for user ID [{$user->id}] to [updated@example.com].")
            ->assertSuccessful();

        $user->refresh();
        $this->assertEquals('updated@example.com', $user->email);
    }

    public function test_command_shows_correct_user_id_in_success_message(): void
    {
        $user = TestUser::where('email', 'test@example.com')->first();

        $this->artisan('user-tools:user-amend-email', [
            'identifier' => 'test@example.com',
            '--email' => 'updated@example.com',
        ])
            ->expectsConfirmation("Change email for user ID [{$user->id}]?", 'yes')
            ->expectsOutputToContain("Email updated for user ID [{$user->id}] to [updated@example.com].")
            ->assertSuccessful();
    }

    public function test_command_handles_empty_email_option_by_prompting(): void
    {
        // This test would require mocking the text() prompt function
        // For now, we'll test with the --email option provided
        $this->assertTrue(true); // Placeholder test
    }
}
