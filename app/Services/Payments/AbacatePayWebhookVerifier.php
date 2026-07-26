<?php

namespace App\Services\Payments;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class AbacatePayWebhookVerifier
{
    /**
     * @throws AuthorizationException
     */
    public function verify(Request $request): void
    {
        $this->verifySecret((string) $request->query('webhookSecret', ''));
        $this->verifySignature(
            signature: (string) $request->header('X-Webhook-Signature', ''),
            body: $request->getContent(),
        );
    }

    /**
     * @throws AuthorizationException
     */
    private function verifySecret(string $incomingSecret): void
    {
        $secret = (string) config('services.abacatepay.webhook_secret');

        if ($secret === '' || ! hash_equals($secret, $incomingSecret)) {
            throw new AuthorizationException('AbacatePay webhook secret invalido.');
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function verifySignature(string $signature, string $body): void
    {
        $publicKey = (string) config('services.abacatepay.webhook_public_key');

        if ($publicKey === '' || $signature === '') {
            throw new AuthorizationException('AbacatePay webhook signature invalida.');
        }

        $expected = base64_encode(hash_hmac('sha256', $body, $publicKey, true));

        if (! hash_equals($expected, $signature)) {
            throw new AuthorizationException('AbacatePay webhook signature invalida.');
        }
    }
}
