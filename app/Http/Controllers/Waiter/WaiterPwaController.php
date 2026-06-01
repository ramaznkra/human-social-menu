<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WaiterPwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        $iconsBase = asset('icons');

        return response()->json([
            'name' => 'Human Social - Garson',
            'short_name' => 'Human Garson',
            'description' => 'Human Social garson paneli',
            'start_url' => url('/waiter/dashboard'),
            'scope' => url('/'),
            'id' => url('/waiter/dashboard'),
            'display' => 'standalone',
            'background_color' => '#121110',
            'theme_color' => '#121110',
            'orientation' => 'portrait',
            'icons' => [
                [
                    'src' => "{$iconsBase}/apple-touch-icon.png",
                    'sizes' => '180x180',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => "{$iconsBase}/icon-192.png",
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => "{$iconsBase}/icon-512.png",
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json; charset=utf-8']);
    }
}
