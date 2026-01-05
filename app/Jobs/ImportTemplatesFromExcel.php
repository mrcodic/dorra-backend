<?php

namespace App\Jobs;

use App\Services\TemplateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportTemplatesFromExcel implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $excelRel, // imports/{batch}/sheet.csv
        public string $zipRel    // imports/{batch}/images.zip
    ) {}

    public function handle(TemplateService $service): void
    {
        // ✅ تحويل للـ absolute path
        $excelAbs = Storage::disk('local')->path($this->excelRel);
        $zipAbs   = Storage::disk('local')->path($this->zipRel);

        if (!file_exists($excelAbs) || !file_exists($zipAbs)) {
            Log::error('Import files missing', [
                'excelRel' => $this->excelRel,
                'zipRel' => $this->zipRel,
                'excelAbs' => $excelAbs,
                'zipAbs' => $zipAbs,
            ]);
            return;
        }

        // ✅ استدعي service function جديدة تستقبل paths
        $service->importExcelFromPaths($excelAbs, $zipAbs);
    }
}


