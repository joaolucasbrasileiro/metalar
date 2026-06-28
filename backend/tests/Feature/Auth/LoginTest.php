<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;

class LoginTest extends TestCase
{

    use RefreshDatabase;

    public function test_exige_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['login', 'password']);

    }

    public function test_login_with_incorrect_credentials(): void
    {
        $this->testCreateUsers();

        $response = $this->postJson('/api/auth/login', [
            'login' => 'testuser@test.com',
            'password' => 'incorrent_pass',
        ]);

        $response
            -> assertStatus(401)
            ->assertJsonPath('message', 'Credenciais invalidas.');

    }

    public function test_right_crendentials_and_returnerd_token(): void
    {
        $user = $this->testCreateUsers();

        $response = $this->postJson('/api/auth/login', [
            'login' => 'testuser@test.com',
            'password' => 'Password@123',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('token_type', 'bearer')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.name', $user->name)
            ->assertJsonPath('user.email', $user->email);

            $token = $response->json('access_token');
            $expiresIn = $response->json('expires_in');

            $this->assertIsString($token);
            $this->assertNotEmpty($token);
            $this->assertIsInt($expiresIn);
            $this->assertGreaterThan(0, $expiresIn);

    }

    public function test_acess_without_credentials(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response
            -> assertStatus(401);
    }

    public function test_authenticated_user_can_acess_me(): void
    {
        $user = $this->testCreateUsers();

        $LoginResponse = $this->postJson('/api/auth/login', [
            'login' => 'testuser@test.com',
            'password' => 'Password@123',
        ]);

        $token = $LoginResponse->json('access_token');

        $response = $this
                    ->withToken($token)
                    ->getJson('/api/auth/me');

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', $user->name)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_acess_without_an_active_user(): void
    {
        $user = $this->testCreateUsers(['is_active'=>false]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'testuser@test.com',
            'password' => 'Password@123',
        ]);

        $response
            ->assertStatus(401)
            ->assertJsonMissingPath('access_token');

    }

    private function testCreateUsers(array $atributtes = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'testuser@test.com',
            'password' => 'Password@123',
            'is_active' => true,
        ], $atributtes));
    }

}
