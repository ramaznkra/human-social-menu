<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuSlideController extends Controller
{
    public function index(): View
    {
        $slides = MenuSlide::orderBy('sort_order')->get();

        return view('admin.menu-slides.index', compact('slides'));
    }

    public function create(): View
    {
        return view('admin.menu-slides.form', ['slide' => new MenuSlide]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['image' => 'required|image|max:5120']);
        $data = $this->validated($request);
        $data['image'] = $request->file('image')->store('menu-slides', 'public');
        MenuSlide::create($data);

        return redirect()->route('admin.menu-slides.index')->with('success', 'Menü slaytı eklendi.');
    }

    public function edit(MenuSlide $menu_slide): View
    {
        return view('admin.menu-slides.form', ['slide' => $menu_slide]);
    }

    public function update(Request $request, MenuSlide $menu_slide): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menu-slides', 'public');
        } else {
            unset($data['image']);
        }
        $menu_slide->update($data);

        return redirect()->route('admin.menu-slides.index')->with('success', 'Menü slaytı güncellendi.');
    }

    public function destroy(MenuSlide $menu_slide): RedirectResponse
    {
        $menu_slide->delete();

        return redirect()->route('admin.menu-slides.index')->with('success', 'Menü slaytı silindi.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:150',
            'subtitle' => 'nullable|string|max:255',
            'type' => 'required|in:venue,guest',
            'duration' => 'required|integer|min:5|max:10',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:5120',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
