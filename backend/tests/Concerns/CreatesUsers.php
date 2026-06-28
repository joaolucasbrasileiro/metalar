<?php

namespace Tests\Concerns;

use App\Enums\UserRole;
use App\Models\User;

trait CreatesUsers
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::ADMIN,
        ], $attributes));
    }
}
