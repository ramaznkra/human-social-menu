<?php

declare(strict_types=1);

$outDir = __DIR__.'/../public/icons';

function makeIcon(int $size, string $path): void
{
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $bg = imagecolorallocate($img, 18, 17, 16);
    imagefilledrectangle($img, 0, 0, $size, $size, $bg);
    $orange = imagecolorallocate($img, 230, 126, 34);
    $white = imagecolorallocate($img, 243, 244, 246);
    $cx = (int) ($size / 2);
    $r = (int) ($size * 0.14);
    imagefilledellipse($img, $cx, (int) ($size * 0.34), $r * 2, $r * 2, $orange);
    imagefilledellipse($img, $cx, (int) ($size * 0.72), (int) ($size * 0.4), (int) ($size * 0.22), $white);
    imagefilledrectangle($img, (int) ($size * 0.37), (int) ($size * 0.64), (int) ($size * 0.63), (int) ($size * 0.68), $orange);
    imagepng($img, $path);
    imagedestroy($img);
}

makeIcon(180, $outDir.'/apple-touch-icon.png');
makeIcon(192, $outDir.'/icon-192.png');
makeIcon(512, $outDir.'/icon-512.png');

echo "PWA icons written to public/icons/\n";
