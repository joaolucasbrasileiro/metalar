<?php

use App\Services\OrderService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:expire-pending', function (): int {
    $expired = app(OrderService::class)->expirePendingOrders();

    $this->info("Expired {$expired} pending orders.");

    return self::SUCCESS;
})->purpose('Expire pending payment orders and release reserved stock');
