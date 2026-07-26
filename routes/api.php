<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentAttemptController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductSkuController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShopSkuPriceController;
use App\Http\Controllers\ShopSkuPromotionController;
use App\Http\Controllers\ShopUserController;
use App\Http\Controllers\StaffOrderController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
 * index -> Listar vários | GET
 * store -> Cadastrar | POST
 * show -> Consultar um | GET
 * update -> Atualizar um | PUT/PATCH
 * destroy -> Delete | DELETE
 */

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
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

Route::prefix('me')
    ->middleware('auth:api')
    ->group(function () {
        Route::get('/favorites', [FavoriteProductController::class, 'index']);
        Route::post('/favorites/{product}', [FavoriteProductController::class, 'store']);
        Route::delete('/favorites/{product}', [FavoriteProductController::class, 'destroy']);
        Route::get('/orders', [OrderController::class, 'index']);
    });

Route::prefix('orders')
    ->middleware('auth:api')
    ->group(function () {
        Route::post('', [OrderController::class, 'store']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
        Route::post('/{order}/payment-attempts', [PaymentAttemptController::class, 'store']);
        Route::post(
            '/{order}/payment-attempts/{paymentAttempt}/fake-approve',
            [PaymentAttemptController::class, 'fakeApprove'],
        );
    });

Route::prefix('cart')
    ->middleware('auth:api')
    ->group(function () {
        Route::get('', [CartController::class, 'show']);
        Route::post('/items', [CartController::class, 'storeItem']);
        Route::patch('/items/{cartItem}', [CartController::class, 'updateItem']);
        Route::delete('/items/{cartItem}', [CartController::class, 'destroyItem']);
        Route::delete('', [CartController::class, 'clear']);
    });

Route::prefix('shops')->group(function () {
    Route::get('', [ShopController::class, 'index']);
    Route::get('/{shop}', [ShopController::class, 'show']);
});

Route::apiResource('brands', BrandController::class)->only(['index', 'show']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('subcategories', SubcategoryController::class)->only(['index', 'show']);
Route::apiResource('product-skus', ProductSkuController::class)
    ->parameters(['product-skus' => 'productSku'])
    ->only(['index', 'show']);

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth:api', 'admin'])
    ->group(function () {
        Route::apiResource('brands', BrandController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('subcategories', SubcategoryController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('product-skus', ProductSkuController::class)
            ->parameters(['product-skus' => 'productSku'])
            ->only(['store', 'update', 'destroy']);
        Route::post('/product-images', [ProductImageController::class, 'store'])
            ->name('product-images.store');
        Route::delete('/product-images/{productImage}', [ProductImageController::class, 'destroy'])
            ->name('product-images.destroy');
        Route::post('/shops/{shop}/users/{user}', [ShopUserController::class, 'store'])
            ->name('shops.users.store');
        Route::delete('/shops/{shop}/users/{user}', [ShopUserController::class, 'destroy'])
            ->name('shops.users.destroy');
    });

Route::prefix('staff')
    ->name('staff.')
    ->middleware(['auth:api', 'shop.staff'])
    ->group(function () {
        Route::get('/shops/{shop}/stocks', [StockController::class, 'index'])
            ->name('stocks.index');
        Route::get('/shops/{shop}/stock-movements', [StockMovementController::class, 'index'])
            ->name('stock-movements.index');
        Route::get('/shops/{shop}/prices', [ShopSkuPriceController::class, 'index'])
            ->name('prices.index');
        Route::get('/shops/{shop}/promotions', [ShopSkuPromotionController::class, 'index'])
            ->name('promotions.index');
        Route::get('/shops/{shop}/orders', [StaffOrderController::class, 'index'])
            ->name('orders.index');
        Route::get('/shops/{shop}/orders/{order}', [StaffOrderController::class, 'show'])
            ->name('orders.show');
        Route::post(
            '/shops/{shop}/product-skus/{productSku}/stock-adjustments',
            [StockAdjustmentController::class, 'store'],
        )->name('stock-adjustments.store');
        Route::put(
            '/shops/{shop}/product-skus/{productSku}/price',
            [ShopSkuPriceController::class, 'update'],
        )->name('prices.update');
        Route::delete(
            '/shops/{shop}/product-skus/{productSku}/price',
            [ShopSkuPriceController::class, 'destroy'],
        )->name('prices.destroy');
        Route::post(
            '/shops/{shop}/product-skus/{productSku}/promotions',
            [ShopSkuPromotionController::class, 'store'],
        )->name('promotions.store');
        Route::delete(
            '/shops/{shop}/promotions/{promotion}',
            [ShopSkuPromotionController::class, 'destroy'],
        )->name('promotions.destroy');
    });
