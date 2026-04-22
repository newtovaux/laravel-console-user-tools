<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Support;

use InvalidArgumentException;

final class PasswordService
{
    public const int MIN_LENGTH = 8;

    private const string LETTERS = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    private const string NUMBERS = '23456789';
    private const string SYMBOLS = '!@#$%^&*()-_=+[]{}?';

    public function generate(int $length, bool $includeSymbols = true): string
    {
        $required = [
            $this->randomCharacter(self::LETTERS),
            $this->randomCharacter(self::NUMBERS),
        ];

        $pool = self::LETTERS . self::NUMBERS;

        if ($includeSymbols) {
            $required[] = $this->randomCharacter(self::SYMBOLS);
            $pool .= self::SYMBOLS;
        }

        while (count($required) < $length) {
            $required[] = $this->randomCharacter($pool);
        }

        shuffle($required);

        return implode('', array_slice($required, 0, $length));
    }

    public function ensureValid(string $password): void
    {
        if (strlen($password) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'Password length must be at least %d characters.',
                self::MIN_LENGTH
            ));
        }
    }

    private function randomCharacter(string $pool): string
    {
        return $pool[random_int(0, strlen($pool) - 1)];
    }
}
