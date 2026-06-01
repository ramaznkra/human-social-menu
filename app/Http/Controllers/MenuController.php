<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CafeGallery;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Table;
use App\Support\MenuLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    /** Genel menü (masa bağlamı yok). */
    public function index(Request $request): View
    {
        return $this->renderMenu($request, null);
    }

    /** Masa QR menüsü — URL: /table/{uuid} */
    public function table(Request $request, string $uuid): View
    {
        return $this->renderMenu($request, $uuid);
    }

    /** Eski /menu/{token} linklerini /table/{uuid} adresine yönlendir. */
    public function legacyTableRedirect(Request $request, string $token, ?string $restaurant = null): RedirectResponse
    {
        $table = Table::withoutGlobalScopes()
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('uuid', $token)->orWhere('qr_token', $token))
            ->first();

        if (! $table?->uuid) {
            abort(404);
        }

        $query = $request->query();
        $target = $restaurant
            ? route('menu.restaurant.table', ['restaurant' => $restaurant, 'uuid' => $table->uuid], false)
            : route('menu.table', ['uuid' => $table->uuid], false);

        if ($query !== []) {
            $target .= '?'.http_build_query($query);
        }

        return redirect()->to($target, 301);
    }

    private function renderMenu(Request $request, ?string $tableKey): View
    {
        $locale = MenuLocale::resolveForMenuPage($request);
        MenuLocale::apply($request, $locale);

        // Masa yalnızca tahmin edilemez UUID (geriye dönük: qr_token) ile çözülür.
        $table = null;

        if ($tableKey) {
            $table = Table::query()
                ->where('is_active', true)
                ->where(fn ($q) => $q->where('uuid', $tableKey)->orWhere('qr_token', $tableKey))
                ->first();

            if (! $table) {
                abort(404, 'Masa bulunamadı.');
            }
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
