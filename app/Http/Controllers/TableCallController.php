<?php

namespace App\Http\Controllers;

use App\Events\TableCallReceived;
use App\Models\Table;
use App\Models\TableCall;
use App\Services\TableStatusService;
use App\Support\MenuLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableCallController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        MenuLocale::apply($request, MenuLocale::resolve($request));

        $validated = $request->validate([
            'table_token' => 'nullable|string',
            'type' => 'required|in:waiter,bill_cash,bill_card,bill',
        ]);

        if ($validated['type'] === 'bill') {
            $validated['type'] = 'bill_cash';
        }

        $table = $this->resolveTable($validated);
        if (! $table) {
            return response()->json(['success' => false, 'message' => __('menu.table_call.table_not_found')], 404);
        }

        $hasActive = TableCall::query()
            ->where('table_id', $table->id)
            ->open()
            ->exists();

        if ($hasActive) {
            return response()->json([
                'success' => true,
                'already_active' => true,
                'active' => true,
                'message' => __('menu.table_call.already'),
            ]);
        }

        $call = TableCall::create([
            'restaurant_id' => $table->restaurant_id,
            'table_id' => $table->id,
            'type' => $validated['type'],
            'status' => TableCall::STATUS_PENDING,
        ]);

        event(new TableCallReceived($call));

        app(TableStatusService::class)->markOccupied($table->id);

        $message = match ($validated['type']) {
            'waiter' => __('menu.table_call.waiter'),
            'bill_cash' => __('menu.table_call.bill_cash'),
            'bill_card' => __('menu.table_call.bill_card'),
            default => __('menu.table_call.default'),
        };

        return response()->json([
            'success' => true,
            'active' => true,
            'message' => $message,
        ]);
    }

    /** Müşteri menüsü: aktif çağrı durumu (garson üstlendi mi?). */
    public function status(Request $request): JsonResponse
    {
        MenuLocale::apply($request, MenuLocale::resolve($request));

        $table = $this->resolveTable([
            'table_token' => $request->query('table_token'),
        ]);

        if (! $table) {
            return response()->json(['active' => false]);
        }

        $call = TableCall::query()
            ->where('table_id', $table->id)
            ->open()
            ->with('waiter:id,name')
            ->first();

        if (! $call) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active' => true,
            'type' => $call->type,
            'status' => $call->status,
            'waiter_name' => $call->waiter?->name,
            'message' => $call->customerMessage(),
        ]);
    }

    private function resolveTable(array $validated): ?Table
    {
        // Masa yalnızca UUID (geriye dönük: qr_token) ile çözülür; sıralı numara kabul edilmez.
        if (! empty($validated['table_token'])) {
            return Table::query()
                ->where('is_active', true)
                ->where(fn ($q) => $q->where('uuid', $validated['table_token'])
                    ->orWhere('qr_token', $validated['table_token']))
                ->first();
        }

        return null;
    }
}
