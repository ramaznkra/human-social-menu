<?php

namespace App\Support;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class MenuLocale
{
    public const LOCALES = ['tr', 'en', 'ru'];

    public const DEFAULT = 'tr';

    public const LABELS = [
        'tr' => 'TR',
        'en' => 'EN',
        'ru' => 'RU',
    ];

    public static function resolve(Request $request): string
    {
        $candidate = $request->input('lang')
            ?? $request->query('lang')
            ?? $request->cookie('menu_lang')
            ?? session('menu_lang', self::DEFAULT);

        return in_array($candidate, self::LOCALES, true) ? $candidate : self::DEFAULT;
    }

    public static function apply(Request $request, string $locale): void
    {
        app()->setLocale($locale);
        session(['menu_lang' => $locale]);
        Cookie::queue('menu_lang', $locale, 60 * 24 * 365);
    }

    public static function menuUrl(?Table $table = null, ?string $locale = null, array $extraQuery = []): string
    {
        $locale = $locale ?? app()->getLocale();
        $query = array_merge(['lang' => $locale], $extraQuery);

        if ($table?->qr_token) {
            $base = route('menu.index', $table->qr_token);
        } else {
            $base = route('menu.index');
            if ($table?->number) {
                $query['masa'] = $table->number;
            }
        }

        return $base.(str_contains($base, '?') ? '&' : '?').http_build_query($query);
    }
}
