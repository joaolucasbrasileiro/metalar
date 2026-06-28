<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    public function test_user_can_delete_own_account(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'api')
            ->deleteJson("/api/users/{$user->id}")
            ->assertStatus(204)
            ->assertNoContent();

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_user_cannot_delete_another_account(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $this->actingAs($user, 'api')
            ->deleteJson("/api/users/{$otherUser->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $otherUser->id,
        ]);
    }

    public function test_delete_returns_not_found_for_unknown_user(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'api')
            ->deleteJson('/api/users/999999')
            ->assertStatus(404);
    }
}
