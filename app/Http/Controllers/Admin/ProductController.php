<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MenuImageOptimizer;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly MenuImageOptimizer $images,
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
        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Ürün eklendi.');
    }

    public function edit(Product $product): View
    {
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
        $product->update($data);

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

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:kitchen,bar',
            'name' => 'required|string|max:150',
            'name_en' => 'nullable|string|max:150',
            'name_ru' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ru' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'badge' => 'nullable|string|max:30',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        return $data;
    }
}
