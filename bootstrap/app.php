<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'lockscreen' => \App\Http\Middleware\LockScreenMiddleware::class,
            'admin' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            return redirect()->back()->with('error', 'Aksi tidak diizinkan atau metode salah.');
        });

        // 2. Tangani Session Habis / CSRF Expired (Error 419)
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            return redirect()->route('login')->with('error', 'Sesi anda telah habis, silakan login kembali.');
        });
    })->create();
