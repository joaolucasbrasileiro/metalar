<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'customer@example.com',
        ]);

        $this->postJson('/api/auth/forgot-password', [
            'email' => ' CUSTOMER@EXAMPLE.COM ',
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Se o email existir, enviaremos instrucoes para redefinir a senha.');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_password_reset_request_does_not_reveal_unknown_email(): void
    {
        Notification::fake();

        $this->postJson('/api/auth/forgot-password', [
            'email' => 'missing@example.com',
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Se o email existir, enviaremos instrucoes para redefinir a senha.');

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'Password@123',
        ]);

        $token = Password::broker()->createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'email' => ' CUSTOMER@EXAMPLE.COM ',
            'token' => $token,
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Senha redefinida com sucesso.');

        $this->assertTrue(Hash::check('NewPassword@123', $user->refresh()->password));
    }

    public function test_password_reset_rejects_invalid_token(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
        ]);

        $this->postJson('/api/auth/reset-password', [
            'email' => 'customer@example.com',
            'token' => 'invalid-token',
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ])
            ->assertStatus(422);
    }
}
