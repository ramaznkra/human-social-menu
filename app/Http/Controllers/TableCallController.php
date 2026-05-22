<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\TableCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableCallController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_token' => 'nullable|string',
            'masa' => 'nullable|string',
            'type' => 'required|in:waiter,bill_cash,bill_card,bill',
        ]);

        if ($validated['type'] === 'bill') {
            $validated['type'] = 'bill_cash';
        }

        $table = $this->resolveTable($validated);
        if (! $table) {
            return response()->json(['success' => false, 'message' => 'Masa bulunamadı.'], 404);
        }

        $hasActive = TableCall::query()
            ->where('table_id', $table->id)
            ->active()
            ->exists();

        if ($hasActive) {
            return response()->json([
                'success' => true,
                'already_active' => true,
                'active' => true,
                'message' => 'Çağrınız zaten iletildi. Garsonumuz masanıza yönlendirildi.',
            ]);
        }

        TableCall::create([
            'table_id' => $table->id,
            'type' => $validated['type'],
            'status' => TableCall::STATUS_ACTIVE,
        ]);

        $message = match ($validated['type']) {
            'waiter' => 'Garsonumuz masanıza yönlendirildi.',
            'bill_cash' => 'Nakit hesap talebiniz alındı. Garsonumuz masanıza geliyor.',
            'bill_card' => 'Kart ile ödeme talebiniz alındı. Pos cihazı masanıza getirilecek.',
            default => 'Talebiniz alındı.',
        };

        return response()->json([
            'success' => true,
            'active' => true,
            'message' => $message,
        ]);
    }

    /** Müşteri menüsü: aktif çağrı hâlâ bekliyor mu? (personel kapattıysa butonlar geri gelir) */
    public function status(Request $request): JsonResponse
    {
        $table = $this->resolveTable([
            'table_token' => $request->query('table_token'),
            'masa' => $request->query('masa'),
        ]);

        if (! $table) {
            return response()->json(['active' => false]);
        }

        $call = TableCall::query()
            ->where('table_id', $table->id)
            ->active()
            ->first();

        return response()->json([
            'active' => $call !== null,
            'type' => $call?->type,
        ]);
    }

    private function resolveTable(array $validated): ?Table
    {
        if (! empty($validated['table_token'])) {
            return Table::query()
                ->where('qr_token', $validated['table_token'])
                ->where('is_active', true)
                ->first();
        }

        if (! empty($validated['masa'])) {
            return Table::query()
                ->where('number', (string) $validated['masa'])
                ->where('is_active', true)
                ->first();
        }

        return null;
    }
}
