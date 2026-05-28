<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Oturum kontrolü (admin + garson). Garsonların /admin yönetim sayfalarına girmesi engellenir.
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('admin_logged_in')) {
            if ($request->expectsJson() || $request->is('admin/api/*', 'api/waiter/*', 'api/admin/*')) {
                return response()->json(['message' => 'Oturum gerekli.'], 401);
            }

            return redirect()->route('admin.login')
                ->with('error', 'Lütfen giriş yapın.');
        }

        $isAdminPanel = $request->is('admin/*')
            && ! $request->is('admin/cikis', 'admin/giris', 'admin/api/*');

        if (session('admin_role') === 'waiter' && $isAdminPanel) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Garson yönetim paneline erişemez.'], 403);
            }

            return redirect()
                ->route('waiter.dashboard')
                ->with('error', 'Bu alan yalnızca yönetici içindir.');
        }

        return $next($request);
    }
}
