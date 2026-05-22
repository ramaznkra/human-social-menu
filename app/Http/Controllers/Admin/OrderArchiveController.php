<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderArchiveController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredArchiveQuery($request);
        $orders = (clone $query)->paginate(20)->withQueryString();
        $filteredTotal = (clone $query)->count();

        return view('admin.orders.archive', [
            'orders' => $orders,
            'filteredTotal' => $filteredTotal,
            'tables' => Table::orderBy('number')->get(['id', 'number']),
        ]);
    }

    public function purge(Request $request): RedirectResponse
    {
        $query = $this->filteredArchiveQuery($request);
        $count = (clone $query)->count();

        if ($count === 0) {
            return back()->with('error', 'Mevcut filtreye uyan silinecek adisyon yok.');
        }

        $ids = (clone $query)->pluck('id');
        Order::query()->whereIn('id', $ids)->delete();

        return redirect()
            ->route('admin.orders.archive')
            ->with('success', "{$count} adisyon arşivden temizlendi.");
    }

    public function destroy(Order $order): RedirectResponse
    {
        if (! in_array($order->status, Order::archivedStatuses(), true)) {
            return back()->with('error', 'Yalnızca tamamlanmış veya iptal edilmiş adisyonlar silinebilir.');
        }

        $number = $order->order_number;
        $order->delete();

        return redirect()
            ->route('admin.orders.archive', request()->only(['q', 'status', 'date_from', 'date_to', 'page']))
            ->with('success', "#{$number} adisyonu silindi.");
    }

    private function filteredArchiveQuery(Request $request): Builder
    {
        $query = Order::query()
            ->archived()
            ->with(['table:id,number'])
            ->orderByDesc('created_at');

        if ($request->filled('status') && in_array($request->status, Order::archivedStatuses(), true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('q')) {
            $term = '%'.trim($request->q).'%';
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', $term)
                    ->orWhereHas('table', fn ($t) => $t->where('number', 'like', $term));
            });
        }

        return $query;
    }
}
