<?php

use App\Services\OrderService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:expire-pending', function (): int {
    $expired = app(OrderService::class)->expirePendingOrders();

    $this->info("Expired {$expired} pending orders.");

    return self::SUCCESS;
})->purpose('Expire pending payment orders and release reserved stock');

Artisan::command('db:ensure-schema', function (): int {
    $connectionName = config('database.default');

    if ($connectionName !== 'pgsql') {
        $this->info("Skipping schema creation for {$connectionName} connection.");

        return self::SUCCESS;
    }

    $schema = trim((string) config('database.connections.pgsql.search_path', 'public'));

    if ($schema === '' || $schema === 'public') {
        $this->info('Skipping schema creation for public schema.');

        return self::SUCCESS;
    }

    if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $schema)) {
        $this->error("Invalid PostgreSQL schema name: {$schema}");

        return self::FAILURE;
    }

    $originalSearchPath = config('database.connections.pgsql.search_path');

    Config::set('database.connections.pgsql.search_path', 'public');
    DB::purge('pgsql');
    DB::connection('pgsql')->statement('create schema if not exists "'.$schema.'"');

    Config::set('database.connections.pgsql.search_path', $originalSearchPath);
    DB::purge('pgsql');

    $this->info("PostgreSQL schema '{$schema}' is ready.");

    return self::SUCCESS;
})->purpose('Create the configured PostgreSQL schema before migrations run');

Schedule::command('orders:expire-pending')->everyMinute();
