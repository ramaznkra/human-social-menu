<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('admin_logged_in')) {
            return redirect()->route('admin.login')
                ->with('error', 'Lütfen giriş yapın.');
        }

        return $next($request);
    }
}
