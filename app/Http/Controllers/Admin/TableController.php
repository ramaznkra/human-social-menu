<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableController extends Controller
{
    public function index(): View
    {
        $tables = Table::orderBy('number')->get();

        return view('admin.tables.index', compact('tables'));
    }

    public function create(): View
    {
        return view('admin.tables.form', ['table' => new Table]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'number' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);
        $data['qr_token'] = Table::generateToken();
        $data['is_active'] = $request->boolean('is_active', true);
        Table::create($data);

        return redirect()->route('admin.tables.index')->with('success', 'Masa eklendi.');
    }

    public function edit(Table $table): View
    {
        return view('admin.tables.form', compact('table'));
    }

    public function update(Request $request, Table $table): RedirectResponse
    {
        $data = $request->validate([
            'number' => 'required|string|max:20',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $table->update($data);

        return redirect()->route('admin.tables.index')->with('success', 'Masa güncellendi.');
    }

    public function destroy(Table $table): RedirectResponse
    {
        $table->delete();

        return redirect()->route('admin.tables.index')->with('success', 'Masa silindi.');
    }

    public function regenerate(Table $table): RedirectResponse
    {
        $table->update(['qr_token' => Table::generateToken()]);

        return back()->with('success', 'QR kodu yenilendi.');
    }
}
