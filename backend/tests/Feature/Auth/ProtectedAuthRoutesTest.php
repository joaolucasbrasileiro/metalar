<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class ProtectedAuthRoutesTest extends TestCase
{
    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/auth/logout')
            ->assertStatus(401);
    }

    public function test_refresh_requires_authentication(): void
    {
        $this->postJson('/api/auth/refresh')
            ->assertStatus(401);
    }
}
