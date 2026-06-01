<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthJwtTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:5JB/m3CUArdtBPJDYYijXloW5M8rf0V+AK2tV+FWIfs=',
            'jwt.secret' => 'testing-secret-for-jwt-authentication',
            'auth.defaults.guard' => 'api',
            'auth.guards.api.driver' => 'jwt',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('Password@123'),
            'cpf' => '12345678901',
            'phone' => '11999999999',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'Password@123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'birthday',
                    'cpf',
                    'phone',
                    'role',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('token_type', 'bearer')
            ->assertJsonPath('user.email', 'user@example.com');
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('Password@123'),
            'cpf' => '12345678901',
            'phone' => '11999999999',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_can_access_me_and_logout(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('Password@123'),
            'cpf' => '12345678901',
            'phone' => '11999999999',
        ]);

        $token = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'Password@123',
        ])->json('access_token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'user@example.com');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertOk();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }
}
