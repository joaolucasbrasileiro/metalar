<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class UpdateCpfTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    public function test_user_can_update_own_cpf(): void
    {
        $user = $this->createUser([
            'cpf' => '52998224725',
        ]);

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$user->id}/cpf", [
                'cpf' => '111.444.777-35',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.cpf', '***.***.***-35');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'cpf' => '11144477735',
        ]);
    }

    public function test_cpf_update_rejects_invalid_cpf(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$user->id}/cpf", [
                'cpf' => '111.111.111-11',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_cpf_update_rejects_duplicate_cpf(): void
    {
        $user = $this->createUser([
            'cpf' => '52998224725',
        ]);
        $otherUser = $this->createUser([
            'cpf' => '11144477735',
        ]);

        $this->actingAs($user, 'api')
            ->patchJson("/api/users/{$user->id}/cpf", [
                'cpf' => $otherUser->cpf,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }
}
