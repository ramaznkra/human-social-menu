<?php

namespace App\Services;

use App\Models\Table;
use Illuminate\Support\Facades\File;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableQrCodeService
{
    public function menuUrl(Table $table): string
    {
        return route('menu.index', ['masa' => $table->number]);
    }

    public function generateFor(Table $table): void
    {
        $directory = storage_path('app/public/qr');
        File::ensureDirectoryExists($directory);

        $url = $this->menuUrl($table);
        $base = 'qr/table-'.$table->id;

        $svgPath = $base.'.svg';
        QrCode::format('svg')
            ->size(320)
            ->margin(2)
            ->errorCorrection('H')
            ->color(38, 38, 38)
            ->backgroundColor(255, 255, 255)
            ->generate($url, storage_path('app/public/'.$svgPath));

        $stored = $svgPath;

        if (extension_loaded('gd') || extension_loaded('imagick')) {
            $pngPath = $base.'.png';
            QrCode::format('png')
                ->size(400)
                ->margin(2)
                ->errorCorrection('H')
                ->color(38, 38, 38)
                ->backgroundColor(255, 255, 255)
                ->generate($url, storage_path('app/public/'.$pngPath));
            $stored = $pngPath;
        }

        $table->update(['qr_image_path' => $stored]);
    }

    public function deleteFor(Table $table): void
    {
        foreach (['.svg', '.png'] as $ext) {
            $path = storage_path('app/public/qr/table-'.$table->id.$ext);
            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }

    public function downloadPath(Table $table, string $format): ?string
    {
        $path = storage_path('app/public/qr/table-'.$table->id.'.'.$format);

        return File::exists($path) ? $path : null;
    }
}
