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
            'type' => 'required|in:waiter,bill',
        ]);

        $table = null;
        if (! empty($validated['table_token'])) {
            $table = Table::query()
                ->select(['id', 'number', 'qr_token', 'is_active'])
                ->where('qr_token', $validated['table_token'])
                ->where('is_active', true)
                ->first();
        } elseif (! empty($validated['masa'])) {
            $table = Table::query()
                ->select(['id', 'number', 'qr_token', 'is_active'])
                ->where('number', $validated['masa'])
                ->where('is_active', true)
                ->first();
        }

        if (! $table) {
            return response()->json(['success' => false, 'message' => 'Masa bulunamadı.'], 404);
        }

        $recent = TableCall::query()
            ->where('table_id', $table->id)
            ->where('type', $validated['type'])
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();

        if ($recent) {
            return response()->json([
                'success' => true,
                'message' => 'Çağrınız zaten iletildi, kısa süre içinde yanınızda olacağız.',
            ]);
        }

        TableCall::create([
            'table_id' => $table->id,
            'type' => $validated['type'],
            'status' => 'pending',
        ]);

        $message = $validated['type'] === 'bill'
            ? 'Hesap talebiniz alındı.'
            : 'Garson çağrınız alındı.';

        return response()->json(['success' => true, 'message' => $message]);
    }
}
