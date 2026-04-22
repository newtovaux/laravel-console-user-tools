<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Tests\Unit;

use InvalidArgumentException;
use Newtovaux\LaravelConsoleUserTools\Support\PasswordService;
use Newtovaux\LaravelConsoleUserTools\Tests\TestCase;

class PasswordServiceTest extends TestCase
{
    public function test_generate_returns_valid_password_with_symbols(): void
    {
        $password = $this->passwords()->generate(16);

        $this->passwords()->ensureValid($password);

        $this->assertSame(16, strlen($password));
        $this->assertMatchesRegularExpression('/[A-Za-z]/', $password);
        $this->assertMatchesRegularExpression('/[2-9]/', $password);
        $this->assertMatchesRegularExpression('/[!@#$%^&*()\-_=+[\]{}?]/', $password);
    }

    public function test_generate_returns_valid_password_without_symbols(): void
    {
        $password = $this->passwords()->generate(16, includeSymbols: false);

        $this->passwords()->ensureValid($password);

        $this->assertSame(16, strlen($password));
        $this->assertMatchesRegularExpression('/[A-Za-z]/', $password);
        $this->assertMatchesRegularExpression('/[2-9]/', $password);
        $this->assertDoesNotMatchRegularExpression('/[!@#$%^&*()\-_=+[\]{}?]/', $password);
    }

    public function test_ensure_valid_rejects_short_passwords(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password length must be at least 8 characters.');

        $this->passwords()->ensureValid('short');
    }

    private function passwords(): PasswordService
    {
        return $this->app->make(PasswordService::class);
    }
}
