<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LockScreenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('locked') === true) {
            $except = ['lockscreen', 'unlock', 'logout'];

            if (!in_array($request->route()->getName(), $except)) {
                return redirect()->route('lockscreen');
            }
        }

        return $next($request);
    }
}
