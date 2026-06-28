<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ListUsersTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    public function test_list_users_requires_authentication(): void
    {
        $this->getJson('/api/users')
            ->assertStatus(401);
    }

    public function test_common_user_cannot_list_users(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'api')
            ->getJson('/api/users')
            ->assertStatus(403);
    }

    public function test_admin_can_list_paginated_users(): void
    {
        $admin = $this->createAdmin();
        User::factory()->count(11)->create();

        $this->actingAs($admin, 'api')
            ->getJson('/api/users')
            ->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'role'],
                ],
                'links',
                'meta',
            ]);
    }
}
