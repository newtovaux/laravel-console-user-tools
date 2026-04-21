<?php

declare(strict_types=1);

namespace Newtovaux\LaravelConsoleUserTools\Tests;

use Newtovaux\LaravelConsoleUserTools\LaravelConsoleUserToolsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up a basic user model for testing
        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelConsoleUserToolsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Configure the user model for testing
        $app['config']->set('auth.providers.users.model', TestUser::class);
        $app['config']->set('user-tools.user_model', TestUser::class);
        $app['config']->set('user-tools.lookup_column', 'email');
    }

    protected function setUpDatabase(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
}