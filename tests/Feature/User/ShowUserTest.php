<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ShowUserTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    public function test_user_can_view_own_profile(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'api')
            ->getJson("/api/users/{$user->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', $user->name)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data._links.update.method', 'PATCH');
    }

    public function test_user_cannot_view_another_profile(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $this->actingAs($user, 'api')
            ->getJson("/api/users/{$otherUser->id}")
            ->assertStatus(403);
    }

    public function test_show_returns_not_found_for_unknown_user(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'api')
            ->getJson('/api/users/999999')
            ->assertStatus(404);
    }
}
