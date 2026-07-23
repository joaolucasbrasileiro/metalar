<?php

namespace Tests\Feature\User;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class UpdateRoleTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    public function test_admin_can_update_user_role(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $this->actingAs($admin, 'api')
            ->patchJson("/api/users/{$user->id}/role", [
                'role' => UserRole::MODERATOR->value,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', UserRole::MODERATOR->value);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => UserRole::MODERATOR->value,
        ]);
    }

    public function test_common_user_cannot_update_roles(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$otherUser->id}/role", [
                'role' => UserRole::ADMIN->value,
            ])
            ->assertStatus(403);
    }

    public function test_role_update_rejects_unknown_role(): void
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $this->actingAs($admin, 'api')
            ->patchJson("/api/users/{$user->id}/role", [
                'role' => 'super-admin',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_role_update_requires_authentication(): void
    {
        $user = $this->createUser();

        $this->patchJson("/api/users/{$user->id}/role", [
            'role' => UserRole::ADMIN->value,
        ])->assertStatus(401);
    }
}
