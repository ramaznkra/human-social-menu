<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (session('admin_logged_in')) {
            return redirect()->to($this->homeForRole(session('admin_role', User::ROLE_ADMIN)));
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->with('error', 'E-posta veya şifre hatalı.');
        }

        session([
            'admin_logged_in' => true,
            'admin_user_id' => $user->id,
            'admin_name' => $user->name,
            'admin_role' => $user->role ?? User::ROLE_ADMIN,
        ]);

        return redirect()->to($this->homeForRole($user->role ?? User::ROLE_ADMIN));
    }

    public function logout(): RedirectResponse
    {
        session()->forget(['admin_logged_in', 'admin_user_id', 'admin_name', 'admin_role']);

        return redirect()->route('admin.login');
    }

    private function homeForRole(string $role): string
    {
        return $role === User::ROLE_WAITER
            ? route('waiter.dashboard')
            : route('admin.dashboard');
    }
}
