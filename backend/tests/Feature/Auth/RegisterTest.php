<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '  Maria da Silva  ',
            'email' => '  MARIA@EXAMPLE.COM  ',
            'password' => 'Password@123',
            'birthday' => '1990-05-20',
            'cpf' => '529.982.247-25',
            'phone' => '(11) 99999-9999',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Conta criada com sucesso.')
            ->assertJsonPath('data.name', 'Maria da Silva')
            ->assertJsonPath('data.email', 'maria@example.com')
            ->assertJsonPath('data.cpf', '***.***.***-25');

        $this->assertDatabaseHas('users', [
            'name' => 'Maria da Silva',
            'email' => 'maria@example.com',
            'cpf' => '52998224725',
            'phone' => '11999999999',
        ]);

        $user = User::where('email', 'maria@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('Password@123', $user->password));
        $this->assertNotSame('Password@123', $user->password);
    }

    public function test_registration_requires_mandatory_fields(): void
    {
        $this->postJson('/api/auth/register', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
                'cpf',
                'phone',
            ]);
    }

    public function test_registration_rejects_invalid_data(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'A',
            'email' => 'not-an-email',
            'password' => 'weak',
            'birthday' => now()->addDay()->format('Y-m-d'),
            'cpf' => '111.111.111-11',
            'phone' => '1234',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
                'birthday',
                'cpf',
                'phone',
            ]);
    }

    public function test_registration_rejects_duplicate_email_cpf_and_phone(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'cpf' => '52998224725',
            'phone' => '11999999999',
        ]);

        $this->postJson('/api/auth/register', [
            'name' => 'Another User',
            'email' => 'EXISTING@EXAMPLE.COM',
            'password' => 'Password@123',
            'cpf' => '529.982.247-25',
            'phone' => '(11) 99999-9999',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'cpf',
                'phone',
            ]);
    }
}
