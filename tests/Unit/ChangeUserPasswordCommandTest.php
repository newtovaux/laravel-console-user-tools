<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Newtovaux\LaravelConsoleUserTools\Tests\TestCase;
use Newtovaux\LaravelConsoleUserTools\Tests\TestUser;
use Symfony\Component\Console\Command\Command;

class ChangeUserPasswordCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'hashedpassword',
        ]);
    }

    public function test_command_fails_with_nonexistent_user_model(): void
    {
        $this->app['config']->set('user-tools.user_model', 'NonExistentModel');

        $exitCode = Artisan::call('user-tools:user-password', ['identifier' => 'test@example.com']);

        $output = Artisan::output();

        $this->assertStringContainsString('Configured user model [NonExistentModel] does not exist.', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }

    public function test_command_fails_with_nonexistent_user(): void
    {
        $exitCode = Artisan::call('user-tools:user-password', ['identifier' => 'nonexistent@example.com']);

        $output = Artisan::output();

        $this->assertStringContainsString('No user found where email = [nonexistent@example.com].', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }

    public function test_command_succeeds_with_valid_user_and_password_option(): void
    {
        $this->artisan('user-tools:user-password', [
            'identifier' => 'test@example.com',
            '--password' => 'newpassword123',
        ])
            ->expectsConfirmation('Change password for user [test@example.com]?', 'yes')
            ->expectsOutputToContain('Password updated for user [test@example.com].')
            ->assertSuccessful();

        $user = TestUser::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_command_works_with_user_id(): void
    {
        $user = TestUser::where('email', 'test@example.com')->first();

        $this->artisan('user-tools:user-password', [
            'identifier' => (string) $user->id,
            '--column' => 'id',
            '--password' => 'newpassword123',
        ])
            ->expectsConfirmation("Change password for user [{$user->id}]?", 'yes')
            ->expectsOutputToContain("Password updated for user [{$user->id}].")
            ->assertSuccessful();

        $user->refresh();

        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_command_generates_password_when_generate_option_used(): void
    {
        $this->artisan('user-tools:user-password', [
            'identifier' => 'test@example.com',
            '--generate' => true,
            '--length' => 20,
        ])
            ->expectsConfirmation('Change password for user [test@example.com]?', 'yes')
            ->expectsOutputToContain('Generated password for test@example.com:')
            ->expectsOutputToContain('Password updated for user [test@example.com].')
            ->assertSuccessful();

        $user = TestUser::where('email', 'test@example.com')->first();

        $this->assertNotSame('hashedpassword', $user->password);
    }

    public function test_command_handles_custom_generated_password_length(): void
    {
        $originalHash = TestUser::where('email', 'test@example.com')->value('password');

        $this->artisan('user-tools:user-password', [
            'identifier' => 'test@example.com',
            '--generate' => true,
            '--length' => 16,
        ])
            ->expectsConfirmation('Change password for user [test@example.com]?', 'no')
            ->expectsOutputToContain('Generated password for test@example.com:')
            ->expectsOutputToContain('Aborted.')
            ->assertExitCode(Command::FAILURE);

        $user = TestUser::where('email', 'test@example.com')->first();

        $this->assertSame($originalHash, $user->password);
    }

    public function test_command_aborts_when_user_declines_confirmation(): void
    {
        $originalHash = TestUser::where('email', 'test@example.com')->value('password');

        $this->artisan('user-tools:user-password', [
            'identifier' => 'test@example.com',
            '--password' => 'newpassword123',
        ])
            ->expectsConfirmation('Change password for user [test@example.com]?', 'no')
            ->expectsOutputToContain('Aborted.')
            ->assertExitCode(Command::FAILURE);

        $user = TestUser::where('email', 'test@example.com')->first();

        $this->assertSame($originalHash, $user->password);
    }

    public function test_command_fails_when_password_option_is_too_short(): void
    {
        $originalHash = TestUser::where('email', 'test@example.com')->value('password');

        $exitCode = Artisan::call('user-tools:user-password', [
            'identifier' => 'test@example.com',
            '--password' => 'short',
        ]);

        $output = Artisan::output();

        $this->assertStringContainsString('Password length must be at least 8 characters.', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertSame($originalHash, TestUser::where('email', 'test@example.com')->value('password'));
    }

    public function test_command_fails_when_generated_password_length_is_too_short(): void
    {
        $originalHash = TestUser::where('email', 'test@example.com')->value('password');

        $exitCode = Artisan::call('user-tools:user-password', [
            'identifier' => 'test@example.com',
            '--generate' => true,
            '--length' => 6,
        ]);

        $output = Artisan::output();

        $this->assertStringContainsString('Password length must be at least 8 characters.', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertSame($originalHash, TestUser::where('email', 'test@example.com')->value('password'));
    }
}
