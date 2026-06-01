<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('users')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::patch('/{user}', [UserController::class, 'update']);
        Route::patch('/{user}/cpf', [UserController::class, 'updateCpf']);
        Route::patch('/{user}/password', [UserController::class, 'updatePassword']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::get('', [UserController::class, 'index']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
        Route::patch('/{user}/role', [UserController::class, 'updateRole']);
    });
});


