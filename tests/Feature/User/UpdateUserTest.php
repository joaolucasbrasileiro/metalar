<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    public function test_user_can_update_own_profile(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$user->id}", [
                'name' => '  Updated Name  ',
                'email' => '  UPDATED@EXAMPLE.COM  ',
                'birthday' => '1992-03-15',
                'phone' => '(71) 99999-8888',
            ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@example.com')
            ->assertJsonPath('data.phone', '(71) 99999-8888');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '71999998888',
        ]);

        $user->refresh();

        $this->assertSame('1992-03-15', $user->birthday->format('Y-m-d'));
    }

    public function test_profile_update_rejects_invalid_data(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$user->id}", [
                'name' => 'A',
                'birthday' => now()->addDay()->format('Y-m-d'),
                'phone' => '1234',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'birthday', 'phone']);
    }

    public function test_profile_update_rejects_duplicate_email_and_phone(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser([
            'email' => 'used@example.com',
            'phone' => '71988887777',
        ]);

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$user->id}", [
                'email' => $otherUser->email,
                'phone' => $otherUser->phone,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'phone']);
    }

    public function test_user_cannot_update_another_profile(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$otherUser->id}", [
                'name' => 'Unauthorized Change',
            ])
            ->assertStatus(403);

        $this->assertDatabaseMissing('users', [
            'id' => $otherUser->id,
            'name' => 'Unauthorized Change',
        ]);
    }
}
