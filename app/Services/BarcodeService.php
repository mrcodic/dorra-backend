<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS1D;


class BarcodeService
{
    // Save PNG (best for thermal printers). Returns public URL.
    public function savePng1D(string $code, string $type = 'C128', int $scale = 3, int $height = 80, string $color = 'black'): string
    {
        $disk = Storage::disk('public');
        $relPath = "barcodes/job-tickets/{$code}.png";

        if (!$disk->exists($relPath)) {
            $png = DNS1D::getBarcodePNG($code, strtoupper($type), $scale, $height, $color);
            // getBarcodePNG returns raw binary string
            $disk->put($relPath, $png);
        }

        return $disk->url($relPath);
    }

    // Save SVG (crisp for PDFs). Returns public URL.
    public function saveSvg1D(string $code, string $type = 'C128', int $width = 2, int $height = 60, string $color = 'black', bool $withText = true): string
    {
        $disk = Storage::disk('public');
        $relPath = "barcodes/job-tickets/{$code}.svg";

        if (!$disk->exists($relPath)) {
            $svg = DNS1D::getBarcodeSVG($code, strtoupper($type), $width, $height, $color, $withText);
            $disk->put($relPath, $svg);
        }

        return $disk->url($relPath);
    }
}
