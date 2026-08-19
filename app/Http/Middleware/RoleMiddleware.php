<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (Auth::check() && Auth::user()->role === $role) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();

            return match ($user->role) {
                'admin' => redirect()->back(),
                default => redirect()->route('login'),
            };
        }

        // Jika belum login
        return redirect()->route('login');
    }
}
