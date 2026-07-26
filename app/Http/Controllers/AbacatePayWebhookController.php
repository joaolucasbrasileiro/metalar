<?php

namespace App\Http\Controllers;

use App\Services\Payments\AbacatePayWebhookService;
use App\Services\Payments\AbacatePayWebhookVerifier;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class AbacatePayWebhookController extends Controller
{
    public function __construct(
        private readonly AbacatePayWebhookVerifier $verifier,
        private readonly AbacatePayWebhookService $webhookService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->verifier->verify($request);

            $event = $this->webhookService->handle($request->json()->all());

            return response()->json([
                'message' => 'Webhook processado.',
                'data' => [
                    'id' => $event->id,
                    'provider_event_id' => $event->provider_event_id,
                    'event' => $event->event,
                    'processed_at' => $event->processed_at,
                ],
            ]);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 401);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Pagamento da AbacatePay nao encontrado.',
            ], 404);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Webhook recebido, mas o pedido nao pode ser atualizado.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (Throwable) {
            return response()->json([
                'message' => 'Nao foi possivel processar o webhook da AbacatePay.',
            ], 500);
        }
    }
}
