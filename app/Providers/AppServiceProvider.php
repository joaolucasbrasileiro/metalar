<?php

namespace App\Providers;

use App\Services\Payments\AbacatePayPaymentGateway;
use App\Services\Payments\FakePaymentGateway;
use App\Services\Payments\PaymentGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            PaymentGateway::class,
            config('services.payment_gateway') === 'abacatepay'
                ? AbacatePayPaymentGateway::class
                : FakePaymentGateway::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
