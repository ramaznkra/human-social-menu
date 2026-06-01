<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOptionGroup;
use App\Services\MenuImageOptimizer;
use App\Services\ProductOptionSyncService;
use App\Support\MenuTranslations;
use App\Support\TenantRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly MenuImageOptimizer $images,
        private readonly ProductOptionSyncService $optionSync,
    ) {}

    public function index(Request $request): View
    {
        $query = Product::with('category')->orderBy('sort_order');
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        $products = $query->get();
        $categories = Category::orderBy('sort_order')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('sort_order')->get();

        return view('admin.products.form', [
            'product' => new Product,
            'categories' => $categories,
            'badgeSuggestions' => $this->badgeSuggestions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $this->images->storeProduct($request->file('image'));
        }
        $data['is_available'] = true;
        $data['in_stock'] = true;

        DB::transaction(function () use ($request, $data) {
            $product = Product::create($data);
            $this->optionSync->sync($product, $request->input('option_groups'));
        });

        return redirect()->route('admin.products.index')->with('success', 'Ürün eklendi.');
    }

    public function edit(Product $product): View
    {
        $product->load(['optionGroups.options']);
        $categories = Category::orderBy('sort_order')->get();

        return view('admin.products.form', [
            'product' => $product,
            'categories' => $categories,
            'badgeSuggestions' => $this->badgeSuggestions(),
        ]);
    }

    /**
     * Hazır rozetler + daha önce kullanılmış rozetler (tekrarsız).
     *
     * @return array<int, string>
     */
    private function badgeSuggestions(): array
    {
        $defaults = ['Popüler', 'Yeni', 'Paket', 'Şefin Önerisi', 'İndirim', 'Acı', 'Vegan', 'Glutensiz'];

        $used = Product::query()
            ->whereNotNull('badge')
            ->where('badge', '!=', '')
            ->distinct()
            ->pluck('badge')
            ->all();

        return collect($defaults)
            ->merge($used)
            ->map(fn ($b) => trim((string) $b))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $this->images->storeProduct($request->file('image'));
        }

        DB::transaction(function () use ($request, $product, $data) {
            $product->update($data);
            $this->optionSync->sync($product, $request->input('option_groups'));
        });

        return redirect()->route('admin.products.index')->with('success', 'Ürün güncellendi.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Ürün silindi.');
    }

    public function toggleAvailability(Product $product): JsonResponse
    {
        $product->update(['is_available' => ! $product->is_available]);

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'is_available' => $product->is_available,
            'label' => $product->is_available ? 'Menüde' : 'Gizli',
        ]);
    }

    public function toggleInStock(Product $product): JsonResponse
    {
        $product->update(['in_stock' => ! $product->in_stock]);

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'in_stock' => $product->in_stock,
            'label' => $product->in_stock ? 'Stokta' : 'Tükendi',
        ]);
    }

    private function validated(Request $request): array
    {
        $translations = MenuTranslations::validated($request);

        $data = $request->validate([
            'category_id' => ['required', TenantRules::existsModel(Category::class)],
            'type' => 'required|in:kitchen,bar',
            'price' => 'required|numeric|min:0',
            'badge' => 'nullable|string|max:30',
            'sort_order' => 'nullable|integer|min:0',
            'in_stock' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
            'option_groups' => 'nullable|array',
            'option_groups.*.id' => 'nullable|integer',
            'option_groups.*.name' => 'nullable|array',
            'option_groups.*.name.tr' => 'nullable|string|max:80',
            'option_groups.*.name.en' => 'nullable|string|max:80',
            'option_groups.*.name.ru' => 'nullable|string|max:80',
            'option_groups.*.type' => 'nullable|in:'.ProductOptionGroup::TYPE_SINGLE.','.ProductOptionGroup::TYPE_MULTIPLE,
            'option_groups.*.required' => 'nullable|boolean',
            'option_groups.*.sort_order' => 'nullable|integer|min:0',
            'option_groups.*.options' => 'nullable|array',
            'option_groups.*.options.*.id' => 'nullable|integer',
            'option_groups.*.options.*.name' => 'nullable|array',
            'option_groups.*.options.*.name.tr' => 'nullable|string|max:80',
            'option_groups.*.options.*.name.en' => 'nullable|string|max:80',
            'option_groups.*.options.*.name.ru' => 'nullable|string|max:80',
            'option_groups.*.options.*.price_modifier' => 'nullable|numeric|min:0',
            'option_groups.*.options.*.is_default' => 'nullable|boolean',
            'option_groups.*.options.*.sort_order' => 'nullable|integer|min:0',
        ]);

        unset($data['option_groups']);

        if ($request->has('in_stock')) {
            $data['in_stock'] = $request->boolean('in_stock');
        }

        return array_merge($data, $translations);
    }
}
