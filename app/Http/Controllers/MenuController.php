<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\MenuSlide;
use App\Models\Setting;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(?string $token = null): View
    {
        $table = null;
        if ($token) {
            $table = Table::where('qr_token', $token)->where('is_active', true)->first();
        }

        $categories = Category::active()
            ->with(['products' => fn ($q) => $q->available()])
            ->get()
            ->filter(fn ($c) => $c->products->isNotEmpty());

        $events = Event::active()
            ->where('event_date', '>=', now()->subDay())
            ->take(5)
            ->get();

        $settings = Setting::allCached();

        $menuSlides = MenuSlide::active()->get();

        return view('menu.index', compact('categories', 'table', 'events', 'settings', 'menuSlides'));
    }
}
