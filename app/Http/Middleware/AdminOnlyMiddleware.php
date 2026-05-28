<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('admin_role') === 'waiter') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Bu alan yalnızca yönetici içindir.'], 403);
            }

            return redirect()
                ->route('waiter.dashboard')
                ->with('error', 'Garson hesabı yönetim paneline erişemez.');
        }

        return $next($request);
    }
}
