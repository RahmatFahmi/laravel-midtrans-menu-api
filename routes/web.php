<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Errors\ErrorPageController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/',  [AuthController::class, 'index'])->name('login');

Route::prefix('admin')->middleware(['auth', 'admin:admin', 'lockscreen'])->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('employees', UserController::class);
    Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
    Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
    Route::resource('menu', MenuItemController::class);
    Route::get('/orders', [OrderReportController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderReportController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/lockscreen', [AuthController::class, 'lockscreen'])->name('lockscreen');
    Route::post('/unlock', [AuthController::class, 'unlock'])->name('unlock');
});

Route::fallback([ErrorPageController::class, 'notFound']);
