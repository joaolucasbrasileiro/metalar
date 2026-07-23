<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
        $this->createUser();

        $response = $this->postJson('/api/auth/login', [
            'login' => 'testuser@test.com',
            'password' => 'incorrent_pass',
        ]);

        $response
            ->assertStatus(401)
            ->assertJsonPath('message', 'Credenciais invalidas.');

    }

    public function test_right_crendentials_and_returnerd_token(): void
    {
        $user = $this->createUser();

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

    public function test_login_with_cpf_and_returnerd_token(): void
    {
        $user = $this->createUser(['cpf' => '52998224725']);

        $response = $this->postJson('/api/auth/login', [
            'login' => '529.982.247-25',
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

    public function test_login_normalizes_email_before_authentication(): void
    {
        $user = $this->createUser();

        $this->postJson('/api/auth/login', [
            'login' => '  TESTUSER@TEST.COM  ',
            'password' => 'Password@123',
        ])
            ->assertStatus(200)
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_acess_without_credentials(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response
            ->assertStatus(401);
    }

    public function test_authenticated_user_can_acess_me(): void
    {
        $user = $this->createUser();

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

    public function test_access_without_an_active_user(): void
    {
        $this->createUser(['is_active' => false]);

        $response = $this->postJson('/api/auth/login', [
            'login' => 'testuser@test.com',
            'password' => 'Password@123',
        ]);

        $response
            ->assertStatus(401)
            ->assertJsonMissingPath('access_token');

    }

    public function test_invalid_acesstoken_cannot_acess_me(): void
    {
        $response = $this
            ->withToken('token-invalido')
            ->getJson('/api/auth/me');

        $response
            ->assertStatus(401);
    }

    public function test_logout_invalidates_acesstoken(): void
    {
        $this->createUser();

        $LoginResponse = $this->postJson('/api/auth/login', [
            'login' => 'testuser@test.com',
            'password' => 'Password@123',
        ]);

        $token = $LoginResponse->json('access_token');

        $logoutResponse = $this
            ->withToken($token)
            ->postJson('/api/auth/logout');

        $logoutResponse
            ->assertStatus(200);

        $response = $this
            ->withToken($token)
            ->getJson('/api/auth/me');

        $response
            ->assertStatus(401);
    }

    public function test_refresh_token_return_acess_token(): void
    {
        $this->createUser();

        $loginResponse = $this->postJson('/api/auth/login', [
            'login' => 'testuser@test.com',
            'password' => 'Password@123',
        ]);

        $loginResponse
            ->assertStatus(200);

        $token = $loginResponse->json('access_token');
        $this->assertIsString($token);

        $rResponse = $this
            ->withToken($token)
            ->postJson('/api/auth/refresh');

        $rResponse
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'expires_in',
                'user',
            ])
            ->assertJsonPath('token_type', 'bearer');

        $nAccessToken = $rResponse->json('access_token');
        $this->assertIsString($nAccessToken);
        $this->assertNotEmpty($nAccessToken);
    }

    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'testuser@test.com',
            'password' => 'Password@123',
            'is_active' => true,
        ], $attributes));
    }
}
