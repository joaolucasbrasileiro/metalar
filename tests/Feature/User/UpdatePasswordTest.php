<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    public function test_user_can_update_own_password(): void
    {
        $user = $this->createUser([
            'password' => 'OldPassword@123',
        ]);

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$user->id}/password", [
                'current_password' => 'OldPassword@123',
                'password' => 'NewPassword@456',
                'password_confirmation' => 'NewPassword@456',
            ])
            ->assertStatus(200);

        $user->refresh();

        $this->assertTrue(Hash::check('NewPassword@456', $user->password));
        $this->assertFalse(Hash::check('OldPassword@123', $user->password));
    }

    public function test_password_update_rejects_wrong_current_password(): void
    {
        $user = $this->createUser([
            'password' => 'OldPassword@123',
        ]);

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$user->id}/password", [
                'current_password' => 'WrongPassword@123',
                'password' => 'NewPassword@456',
                'password_confirmation' => 'NewPassword@456',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_password_update_requires_confirmation_and_strong_password(): void
    {
        $user = $this->createUser([
            'password' => 'OldPassword@123',
        ]);

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$user->id}/password", [
                'current_password' => 'OldPassword@123',
                'password' => 'weak',
                'password_confirmation' => 'different',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_update_rejects_current_password_as_new_password(): void
    {
        $user = $this->createUser([
            'password' => 'OldPassword@123',
        ]);

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$user->id}/password", [
                'current_password' => 'OldPassword@123',
                'password' => 'OldPassword@123',
                'password_confirmation' => 'OldPassword@123',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_update_another_users_password(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser([
            'password' => 'OtherPassword@123',
        ]);

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$otherUser->id}/password", [
                'current_password' => 'OtherPassword@123',
                'password' => 'ChangedPassword@456',
                'password_confirmation' => 'ChangedPassword@456',
            ])
            ->assertStatus(403);

        $otherUser->refresh();

        $this->assertTrue(Hash::check('OtherPassword@123', $otherUser->password));
    }
}
