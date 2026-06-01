<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\CurrentRestaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WaiterController extends Controller
{
    public function index(): View
    {
        $waiters = User::query()
            ->where('role', User::ROLE_WAITER)
            ->where('restaurant_id', CurrentRestaurant::id())
            ->orderBy('name')
            ->get();

        return view('admin.waiters.index', compact('waiters'));
    }

    public function create(): View
    {
        return view('admin.waiters.form', ['waiter' => new User(['role' => User::ROLE_WAITER, 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8|max:72',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_WAITER,
            'restaurant_id' => CurrentRestaurant::id(),
            'is_active' => true,
        ]);

        return redirect()->route('admin.waiters.index')->with('success', 'Garson hesabı oluşturuldu.');
    }

    public function edit(User $waiter): View
    {
        return view('admin.waiters.form', compact('waiter'));
    }

    public function update(Request $request, User $waiter): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($waiter->id),
            ],
            'password' => 'nullable|string|min:8|max:72',
        ]);

        $waiter->name = $data['name'];
        $waiter->email = $data['email'];

        if (! empty($data['password'])) {
            $waiter->password = $data['password'];
        }

        $waiter->save();

        return redirect()->route('admin.waiters.index')->with('success', 'Garson bilgileri güncellendi.');
    }

    public function destroy(User $waiter): RedirectResponse
    {
        if ((int) session('admin_user_id') === (int) $waiter->id) {
            return back()->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        $waiter->delete();

        return redirect()->route('admin.waiters.index')->with('success', 'Garson hesabı silindi.');
    }

    public function toggleActive(User $waiter): JsonResponse
    {
        if ((int) session('admin_user_id') === (int) $waiter->id) {
            return response()->json([
                'success' => false,
                'message' => 'Kendi hesabınızı pasife alamazsınız.',
            ], 422);
        }

        $waiter->update(['is_active' => ! $waiter->is_active]);

        return response()->json([
            'success' => true,
            'waiter_id' => $waiter->id,
            'is_active' => $waiter->is_active,
            'label' => $waiter->is_active ? 'Aktif' : 'Pasif',
        ]);
    }
}
