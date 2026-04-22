# Laravel Console User Tools

Artisan commands for generating passwords, changing user passwords, listing users, and amending user email addresses in Laravel 12 applications.

## Installation

This package currently targets Laravel 12 and above, and PHP 8.3+.

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
php artisan user-tools:create --copy
php artisan user-tools:create --length=32 --no-symbols --copy
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

## Practical notes

First, this package assumes your user table has a `password` column and your configured user model is Eloquent-backed, which is the normal Laravel setup.

Second, the application’s configured auth user model by default so the package stays reusable across projects instead of hard-coding `App\Models\User`. That aligns well with Laravel’s package and auth conventions.
