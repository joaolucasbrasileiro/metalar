<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ActivateAccountNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_activate_account_with_valid_signed_link(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'pending@example.com',
            'is_active' => false,
        ]);

        $activationUrl = URL::temporarySignedRoute(
            'auth.activate',
            now()->addMinutes(60),
            [
                'user' => $user->id,
                'hash' => sha1($user->email),
            ],
        );

        $this->getJson($activationUrl)
            ->assertStatus(200)
            ->assertJsonPath('message', 'Conta ativada com sucesso.');

        $user->refresh();

        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_activation_rejects_signed_link_with_wrong_email_hash(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'pending@example.com',
            'is_active' => false,
        ]);

        $activationUrl = URL::temporarySignedRoute(
            'auth.activate',
            now()->addMinutes(60),
            [
                'user' => $user->id,
                'hash' => sha1('other@example.com'),
            ],
        );

        $this->getJson($activationUrl)
            ->assertStatus(403)
            ->assertJsonPath('message', 'Link de ativacao invalido ou expirado.');

        $this->assertFalse($user->refresh()->is_active);
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_can_request_activation_email_again(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create([
            'email' => 'pending@example.com',
            'is_active' => false,
        ]);

        $this->postJson('/api/auth/activation/resend', [
            'email' => ' PENDING@EXAMPLE.COM ',
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Se a conta existir e precisar de ativacao, enviaremos um novo email.');

        Notification::assertSentTo($user, ActivateAccountNotification::class);
    }

    public function test_activation_resend_does_not_send_email_to_active_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'active@example.com',
            'is_active' => true,
        ]);

        $this->postJson('/api/auth/activation/resend', [
            'email' => 'active@example.com',
        ])
            ->assertStatus(200)
            ->assertJsonPath('message', 'Se a conta existir e precisar de ativacao, enviaremos um novo email.');

        Notification::assertNotSentTo($user, ActivateAccountNotification::class);
    }
}
