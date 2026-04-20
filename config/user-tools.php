<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | Defaults to the application's configured auth user model.
    |
    */
    'user_model' => env(
        'USER_TOOLS_USER_MODEL',
        config('auth.providers.users.model', App\Models\User::class)
    ),

    /*
    |--------------------------------------------------------------------------
    | Default lookup column
    |--------------------------------------------------------------------------
    |
    | The column used to find a user when changing passwords.
    |
    */
    'lookup_column' => env('USER_TOOLS_LOOKUP_COLUMN', 'email'),

    /*
    |--------------------------------------------------------------------------
    | Generated password length
    |--------------------------------------------------------------------------
    */
    'generated_length' => (int) env('USER_TOOLS_GENERATED_LENGTH', 20),
];
