# Laravel Console User Tools

Artisan commands for generating passwords and changing user passwords in Laravel 13.

## Installation

```bash
composer require newtovaux/laravel-console-user-tools
php artisan vendor:publish --tag=user-tools-config
```

## Commands

### Generate a password

```bash
php artisan user-tools:create
php artisan user-tools:create --length=24
php artisan user-tools:create --no-symbols
```

### Change a user's password

```bash
php artisan user-tools:user-password dan@example.com
php artisan user-tools:user-password dan@example.com --generate
php artisan user-tools:user-password 15 --column=id
```

## Practical notes

First, this package assumes your user table has a `password` column and your configured user model is Eloquent-backed, which is the normal Laravel setup.

Second, the application’s configured auth user model by default so the package stays reusable across projects instead of hard-coding `App\Models\User`. That aligns well with Laravel’s package and auth conventions.
