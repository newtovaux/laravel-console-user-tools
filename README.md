# Laravel Console User Tools

Artisan commands for generating passwords, changing user passwords, listing users, and amending user email addresses in Laravel 12 applications.

## Why?

If you've ever had to jump onto a host, or into a container, because you don't have UI access (or if there's an issue) and you just need to quickly reset a password or alter a user somehow. If you don't want to memorise a bunch of SQL commands. If you just need to do user management quickly and easily.

... then perhaps this little package can help.

## Installation

Available on Packagist: https://packagist.org/packages/newtovaux/laravel-console-user-tools

Source code: https://github.com/newtovaux/laravel-console-user-tools

This package currently targets Laravel 12 and above (tested on 13 too), and PHP 8.3+.

```bash
composer require newtovaux/laravel-console-user-tools
```

### Optional

```bash
php artisan vendor:publish --tag=user-tools-config
```

## Commands

### Generate a password

```bash
php artisan user-tools:create
php artisan user-tools:create --length=24
php artisan user-tools:create --no-symbols
php artisan user-tools:create --length=32 --no-symbols
```

### Change a user's password

```bash
php artisan user-tools:user-password dan@example.com
php artisan user-tools:user-password dan@example.com --password='new-password-123'
php artisan user-tools:user-password dan@example.com --generate
php artisan user-tools:user-password dan@example.com --generate --length=24
php artisan user-tools:user-password 15 --column=id
php artisan user-tools:user-password 15 --column=id --password='new-password-123'
```

### List all users

```bash
php artisan user-tools:list-users
php artisan user-tools:list-users --limit=50
```

### Change a user's email address

```bash
php artisan user-tools:user-amend-email dan@example.com
php artisan user-tools:user-amend-email 15 --column=id
php artisan user-tools:user-amend-email dan@example.com --email=new@example.com
php artisan user-tools:user-amend-email 15 --column=id --email=new@example.com
```

## Configuration

If you want to override the defaults, publish the config file:

```bash
php artisan vendor:publish --tag=user-tools-config
```

Available options:

```php
return [
    'user_model' => env('USER_TOOLS_USER_MODEL', config('auth.providers.users.model', App\Models\User::class)),
    'lookup_column' => env('USER_TOOLS_LOOKUP_COLUMN', 'email'),
    'generated_length' => (int) env('USER_TOOLS_GENERATED_LENGTH', 20),
];
```

These settings let you:

- use a custom Eloquent user model
- change the default lookup column from `email` to something else
- set a default generated password length for `user-tools:create`

## Practical notes

First, this package assumes your user table has a `password` column and your configured user model is Eloquent-backed, which is the normal Laravel setup.

Second, the package uses the application's configured auth user model by default so it stays reusable across projects instead of hard-coding `App\Models\User`. That aligns well with Laravel's package and auth conventions.

Third, the write commands are intentionally interactive. `user-tools:user-password` and `user-tools:user-amend-email` both ask for confirmation before saving changes.

Fourth, `user-tools:user-amend-email` will try the configured lookup column first and then fall back to `id` lookup when appropriate.

Fifth, `user-tools:list-users` outputs `id` and `email`, ordered by `id`, and supports `--limit` for large tables.

## Security notes

Passing secrets directly on the command line can leak them into shell history. On shared machines or production hosts, prompting for a password is usually safer than using `--password='...'`.

The `--copy` option on `user-tools:create` is currently a placeholder and does not copy to the clipboard yet.

## Tests

Install development dependencies, then run the test suite with Composer:

```bash
composer install
composer test
```

The package tests run with PHPUnit 11 and Orchestra Testbench against an in-memory SQLite database, so no separate test database setup is required.

## License

This software is released under the MIT license.
