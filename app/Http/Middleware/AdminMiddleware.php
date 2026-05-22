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
            if ($request->expectsJson() || $request->is('admin/api/*')) {
                return response()->json(['message' => 'Oturum gerekli.'], 401);
            }

            return redirect()->route('admin.login')
                ->with('error', 'Lütfen giriş yapın.');
        }

        return $next($request);
    }
}
