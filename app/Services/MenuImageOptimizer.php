<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menü / slider görsellerini yüklemeden önce küçültür (GD).
 * ext-gd yoksa dosyayı olduğu gibi kaydeder.
 */
class MenuImageOptimizer
{
    public function storeGallery(UploadedFile $file): string
    {
        return $this->store($file, 'cafe-galleries', 1200, 800, 80);
    }

    public function storeProduct(UploadedFile $file): string
    {
        return $this->store($file, 'products', 800, 800, 82);
    }

    public function storeCategory(UploadedFile $file): string
    {
        return $this->store($file, 'categories', 1000, 600, 82);
    }

    public function store(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1200,
        int $maxHeight = 1200,
        int $jpegQuality = 82,
    ): string {
        if (! extension_loaded('gd')) {
            return $file->store($directory, 'public');
        }

        $source = $this->loadImage($file->getRealPath(), $file->getMimeType());
        if ($source === null) {
            return $file->store($directory, 'public');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        [$newWidth, $newHeight] = $this->scaledDimensions($width, $height, $maxWidth, $maxHeight);

        $canvas = $newWidth !== $width || $newHeight !== $height
            ? imagescale($source, $newWidth, $newHeight)
            : $source;

        if ($canvas === false) {
            imagedestroy($source);

            return $file->store($directory, 'public');
        }

        Storage::disk('public')->makeDirectory($directory);
        $relativePath = $directory.'/'.Str::uuid().'.jpg';
        $fullPath = Storage::disk('public')->path($relativePath);

        $saved = imagejpeg($canvas, $fullPath, $jpegQuality);

        if ($canvas !== $source) {
            imagedestroy($canvas);
        }
        imagedestroy($source);

        if (! $saved) {
            return $file->store($directory, 'public');
        }

        return $relativePath;
    }

    private function loadImage(string $path, string $mime): ?\GdImage
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path) ?: null,
            'image/png' => $this->loadPng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            'image/gif' => @imagecreatefromgif($path) ?: null,
            default => null,
        };
    }

    private function loadPng(string $path): ?\GdImage
    {
        $img = @imagecreatefrompng($path);
        if ($img === false) {
            return null;
        }
        imagealphablending($img, true);
        imagesavealpha($img, true);

        return $img;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function scaledDimensions(int $width, int $height, int $maxWidth, int $maxHeight): array
    {
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return [$width, $height];
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }
}
