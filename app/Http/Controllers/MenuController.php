<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CafeGallery;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Table;
use App\Support\MenuLocale;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request, ?string $token = null): View
    {
        $locale = MenuLocale::resolveForMenuPage($request);
        MenuLocale::apply($request, $locale);

        // Masa yalnızca tahmin edilemez UUID (geriye dönük: qr_token) ile çözülür.
        // Sıralı numara (?masa=) ile erişim güvenlik gereği desteklenmez.
        $table = null;

        if ($token) {
            $table = Table::query()
                ->where('is_active', true)
                ->where(fn ($q) => $q->where('uuid', $token)->orWhere('qr_token', $token))
                ->first();
        }

        $categories = Category::active()
            ->with([
                'products' => fn ($q) => $q->available()->with([
                    'optionGroups' => fn ($q) => $q->orderBy('sort_order'),
                    'optionGroups.options' => fn ($q) => $q->orderBy('sort_order'),
                ]),
            ])
            ->get();

        $settings = Setting::allCached();

        $spottedSliders = CafeGallery::active()->get();

        $productPopularity = OrderItem::query()
            ->whereHas('order', fn ($q) => $q->whereDate('created_at', today()))
            ->whereNotNull('product_id')
            ->select('order_items.product_id', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('order_items.product_id')
            ->pluck('total_qty', 'product_id');

        return view('menu.index', compact(
            'categories',
            'table',
            'settings',
            'spottedSliders',
            'productPopularity',
            'locale',
        ));
    }
}
