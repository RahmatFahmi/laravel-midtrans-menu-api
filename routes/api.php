<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\API\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MidtransCallbackController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/register-device', [DeviceController::class, 'registerDevice']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/menu/rate', [MenuItemController::class, 'storeRating']);
    Route::post('/menu/favorite', [MenuItemController::class, 'toggleFavorite']);

    Route::post('menu/checkout', [OrderController::class, 'checkout']);
    Route::get('/api/orders', [OrderController::class, 'getOrders']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::post('/orders/{id}/repay', [OrderController::class, 'repayOrder']);
    Route::post('/orders/{id}/process', [OrderController::class, 'processOrder']);
    Route::post('/orders/{id}/finish', [OrderController::class, 'finishOrder']);
    Route::post('/orders/{id}/confirm-manual-payment', [OrderController::class, 'confirmPayment']);
});
Route::post('/connect-table', [AuthController::class, 'connectToTable']);
Route::post('/midtrans/callback', MidtransCallbackController::class);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/menu', [MenuItemController::class, 'index']);
Route::get('/menu/{id}', [MenuItemController::class, 'show']);
