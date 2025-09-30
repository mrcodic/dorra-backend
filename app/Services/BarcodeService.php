<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS1D; // concrete class (non-static)

class BarcodeService
{
    // Save PNG (best for thermal printers). Returns public URL.
    public function savePng1D(
        string $code,
        string $type = 'C128',
        int $scale = 3,
        int $height = 80,
        array $color = [0, 0, 0] // RGB array for PNG
    ): string {
        $disk = Storage::disk('public');
        $relPath = "barcodes/job-tickets/{$code}.png";

        if (!$disk->exists($relPath)) {
            $gen = new DNS1D(); // instantiate
            $png = $gen->getBarcodePNG(strtoupper($code), strtoupper($type), $scale, $height, $color);
            $disk->put($relPath, $png); // raw bytes
        }

        return $disk->url($relPath);
    }

    // Save SVG (crisp for PDFs). Returns public URL.
    public function saveSvg1D(
        string $code,
        string $type = 'C128',
        int $width = 2,
        int $height = 60,
        string $color = 'black',
        bool $withText = true
    ): string {
        $disk = Storage::disk('public');
        $relPath = "barcodes/job-tickets/{$code}.svg";

        if (!$disk->exists($relPath)) {
            $gen = new DNS1D(); // instantiate
            $svg = $gen->getBarcodeSVG($code, strtoupper($type), $width, $height, $color, $withText);
            $disk->put($relPath, $svg);
        }

        return $disk->url($relPath);
    }
}
