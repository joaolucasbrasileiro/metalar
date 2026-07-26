<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentAttemptRequest;
use App\Http\Resources\PaymentAttemptResource;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Services\PaymentAttemptService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class PaymentAttemptController extends Controller
{
    public function store(
        StorePaymentAttemptRequest $request,
        Order $order,
        PaymentAttemptService $service,
    ): PaymentAttemptResource {
        $this->authorizeOwner($order);

        return new PaymentAttemptResource(
            $service->createOrReuse(
                $order,
                $service->inputFromValidatedData($request->validated()),
            )
        );
    }

    public function fakeApprove(
        Order $order,
        PaymentAttempt $paymentAttempt,
        PaymentAttemptService $service,
    ): PaymentAttemptResource {
        abort_unless(App::environment(['local', 'testing']), 404);
        $this->authorizeOwner($order);
        abort_unless($paymentAttempt->order_id === $order->id, 404);

        return new PaymentAttemptResource($service->approve($paymentAttempt));
    }

    private function authorizeOwner(Order $order): void
    {
        abort_unless(
            $order->user_id === Auth::guard('api')->id(),
            404,
        );
    }
}
