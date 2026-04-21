<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Newtovaux\LaravelConsoleUserTools\Tests\TestCase;
use Symfony\Component\Console\Command\Command;

class CreatePasswordCommandTest extends TestCase
{
    public function test_command_generates_password_with_default_length(): void
    {
        $exitCode = Artisan::call('user-tools:create');
        $output = Artisan::output();
        preg_match('/Generated password:\s+([^\n]+)/', $output, $matches);

        $this->assertStringContainsString('Generated password:', $output);
        $this->assertSame(20, strlen($matches[1] ?? ''));
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function test_command_generates_password_with_custom_length(): void
    {
        $exitCode = Artisan::call('user-tools:create', ['--length' => 16]);
        $output = Artisan::output();
        preg_match('/Generated password:\s+([^\n]+)/', $output, $matches);

        $this->assertStringContainsString('Generated password:', $output);
        $this->assertSame(16, strlen($matches[1] ?? ''));
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function test_command_fails_with_length_less_than_8(): void
    {
        $exitCode = Artisan::call('user-tools:create', ['--length' => 6]);

        $output = Artisan::output();

        $this->assertStringContainsString('Password length must be at least 8 characters.', $output);
        $this->assertEquals(Command::FAILURE, $exitCode);
    }

    public function test_command_generates_password_without_symbols(): void
    {
        $exitCode = Artisan::call('user-tools:create', ['--no-symbols' => true]);
        $output = Artisan::output();
        preg_match('/Generated password:\s+([^\n]+)/', $output, $matches);

        $this->assertStringContainsString('Generated password:', $output);
        $this->assertDoesNotMatchRegularExpression('/[!@#$%^&*()\-_=+[\]{}?]/', $matches[1] ?? '');
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function test_command_handles_copy_option(): void
    {
        $exitCode = Artisan::call('user-tools:create', ['--copy' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('Clipboard copy is not implemented', $output);
        $this->assertEquals(Command::SUCCESS, $exitCode);
    }

    public function test_generated_password_contains_required_character_types(): void
    {
        Artisan::call('user-tools:create', ['--length' => 20]);
        $output = Artisan::output();

        preg_match('/Generated password:\s+([^\n]+)/', $output, $matches);
        $password = $matches[1] ?? '';

        $this->assertNotEmpty($password);
        $this->assertSame(20, strlen($password));
        $this->assertMatchesRegularExpression('/[A-Za-z]/', $password, 'Password should contain letters');
        $this->assertMatchesRegularExpression('/[2-9]/', $password, 'Password should contain numbers');
        $this->assertMatchesRegularExpression('/[!@#$%^&*()\-_=+[\]{}?]/', $password, 'Password should contain symbols');
    }

    public function test_generated_password_without_symbols_excludes_symbols(): void
    {
        Artisan::call('user-tools:create', ['--no-symbols' => true, '--length' => 20]);

        $output = Artisan::output();

        preg_match('/Generated password:\s+([^\n]+)/', $output, $matches);
        $password = $matches[1] ?? '';

        $this->assertNotEmpty($password);
        $this->assertSame(20, strlen($password));
        $this->assertMatchesRegularExpression('/[A-Za-z]/', $password, 'Password should contain letters');
        $this->assertMatchesRegularExpression('/[2-9]/', $password, 'Password should contain numbers');
        $this->assertDoesNotMatchRegularExpression('/[!@#$%^&*()\-_=+[\]{}?]/', $password, 'Password should not contain symbols');
    }
}
