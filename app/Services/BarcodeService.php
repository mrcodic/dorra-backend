<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;

class BarcodeService
{
    // ------------ 1D (Code 128) ------------
    public function savePng1D(
        string $code,
        string $type = 'C128',
        int $scale = 4,
        int $height = 120,
        array $color = [0, 0, 0]
    ): string {
        $disk = Storage::disk('public');
        $relPath = "barcodes/job-tickets/{$code}.png";

        if (!$disk->exists($relPath)) {
            $gen = new DNS1D();
            // getBarcodePNG returns BASE64 for PNG → decode before saving
            $pngBase64 = $gen->getBarcodePNG(strtoupper($code), strtoupper($type), $scale, $height, $color);
            $disk->put($relPath, base64_decode($pngBase64));
        }

        return $disk->url($relPath);
    }

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
            $gen = new DNS1D();
            $svg = $gen->getBarcodeSVG($code, strtoupper($type), $width, $height, $color, $withText);
            $disk->put($relPath, $svg); // SVG is plain text
        }

        return $disk->url($relPath);
    }

    // ------------ 2D (QR Code) ------------
    public function savePngQR(
        string $text,
        int $scale = 6,              // pixel scaling for both axes
        array $color = [0, 0, 0]
    ): string {
        $disk = Storage::disk('public');
        $relPath = "barcodes/job-tickets/qr-{$this->slug($text)}.png";

        if (!$disk->exists($relPath)) {
            $gen = new DNS2D();
            // PNG is BASE64 → decode
            $pngBase64 = $gen->getBarcodePNG($text, 'QRCODE', $scale, $scale, $color);
            $disk->put($relPath, base64_decode($pngBase64));
        }

        return $disk->url($relPath);
    }

    public function saveSvgQR(
        string $text,
        int $width = 4,              // module width
        int $height = 4,             // module height
        string $color = 'black'
    ): string {
        $disk = Storage::disk('public');
        $relPath = "barcodes/job-tickets/qr-{$this->slug($text)}.svg";

        if (!$disk->exists($relPath)) {
            $gen = new DNS2D();
            $svg = $gen->getBarcodeSVG($text, 'QRCODE', $width, $height, $color);
            $disk->put($relPath, $svg);
        }

        return $disk->url($relPath);
    }

    // Optional helper to keep filenames safe/short
    protected function slug(string $value): string
    {
        $v = preg_replace('/[^A-Za-z0-9\-_.]+/', '-', $value);
        return trim(substr($v ?? 'qr', 0, 120), '-');
    }
}
